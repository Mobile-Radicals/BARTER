package com.trade.barter;

import android.app.ActionBar;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.PendingIntent;
import android.app.ProgressDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.graphics.Typeface;
import android.graphics.drawable.ColorDrawable;
import android.net.Uri;
import android.nfc.NfcAdapter;
import android.nfc.tech.IsoDep;
import android.nfc.tech.MifareClassic;
import android.nfc.tech.MifareUltralight;
import android.nfc.tech.Ndef;
import android.nfc.tech.NdefFormatable;
import android.nfc.tech.NfcA;
import android.nfc.tech.NfcB;
import android.nfc.tech.NfcF;
import android.nfc.tech.NfcV;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import com.trade.barter.utils.DatabaseHandler;
import com.trade.barter.utils.Redeem;
import com.trade.barter.utils.Transaction;
import com.trade.barter.utils.Utils;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.Iterator;

import google.zxing.integration.android.IntentIntegrator;
import google.zxing.integration.android.IntentResult;

public class MainActivity extends Activity {

    private NfcAdapter adapter;
    private PendingIntent nfcPendingIntent;
    private IntentFilter[] readTagFilters;
    private String[][] mTechLists;

    private ActionBar actionBar;
    private AlertDialog dialog = null;
    boolean dialogOpened = false;
    boolean isRedeem = false, isTransaction = false;
    private SharedPreferences settings;
    private SharedPreferences.Editor editor;
    private DatabaseHandler db;
    private MenuItem syncItem;
    private String interactionType;

    private ArrayList<Transaction> transactions;
    private ArrayList<Redeem> allRedeems;
    private ProgressDialog uploadDialog;
    private String[] params;
    private int timeToCheck = 72;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        //declare an NFC adapter
        adapter = NfcAdapter.getDefaultAdapter(this);

        //check if NFC is enabled
        boolean nfcEnabled = adapter.isEnabled();
        if(!nfcEnabled){
            Toast.makeText(getApplicationContext(), "Please activate NFC then press Back to return to the application!", Toast.LENGTH_LONG).show();
            startActivityForResult(new Intent(android.provider.Settings.ACTION_WIRELESS_SETTINGS), 0);
        }

        Button transactionButton = (Button) findViewById(R.id.transactionBtn);
        Typeface font = Typeface.createFromAsset(getAssets(), "square.ttf");
        transactionButton.setTypeface(font);
        transactionButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                transactionAlert();
            }
        });

        Button syncButton = (Button) findViewById(R.id.activityBtn);
        Typeface font1 = Typeface.createFromAsset(getAssets(), "square.ttf");
        syncButton.setTypeface(font1);
        syncButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(new Intent(getApplicationContext(), SyncActivity.class));
            }
        });

        Button profileButton = (Button) findViewById(R.id.profileBtn);
        Typeface font2 = Typeface.createFromAsset(getAssets(), "square.ttf");
        profileButton.setTypeface(font2);
        profileButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(new Intent(getApplicationContext(), ProfileActivity.class));
            }
        });

        Button loyaltyButton = (Button) findViewById(R.id.redeemBtn);
        Typeface font3 = Typeface.createFromAsset(getAssets(), "square.ttf");
        loyaltyButton.setTypeface(font3);
        loyaltyButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                redeemAlert();
            }
        });

        actionBar = this.getActionBar();
        actionBar.setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.charcole_gray)));

        settings = this.getSharedPreferences(getString(R.string.preferences), 0);
        //save the type of the transaction to the shared preferences object
        editor = settings.edit();
        db = new DatabaseHandler(this.getApplicationContext());

        try {
            if(settings.getBoolean("firstLogin", false) == false){
                editor.putBoolean("firstLogin", true);
                editor.commit();
            }
            else{
                checkTimeSinceLastUpdate();
            }
        } catch (ParseException e) {
            e.printStackTrace();
        }
    }

    public void scanCardDialog(String interactionType){

        AlertDialog.Builder dialog = new AlertDialog.Builder(this);
        //dialog.setView(getLayoutInflater().inflate(R.layout.alert_dialog, null));
        if (interactionType == "transaction")
        {
            dialog.setView(getLayoutInflater().inflate(R.layout.alert_dialog, null));
        }
        else if (interactionType == "redeem")
        {
            dialog.setView(getLayoutInflater().inflate(R.layout.alert_dialog_redeem, null));
        }
        this.dialog = dialog.create();
        this.dialog.show();
        dialogOpened = true;
    }

    @Override
    protected void onNewIntent(Intent intent){

        //get the byte[] from the NFC card
        byte[] nfcTag = intent.getByteArrayExtra(NfcAdapter.EXTRA_ID);
        //convert the byte array to String
        String nfcCardID = Utils.ByteArrayToHexString(nfcTag);
        //if the application detects an NFC event and the alert window is opened - navigate to the registration of the NFC card
        if (intent.getAction().equals(NfcAdapter.ACTION_TECH_DISCOVERED)){
            Intent i = new Intent();
            i.putExtra("nfcCardID", nfcCardID);

            if(isRedeem){
                isRedeem = false;

                if(db.getConsumerData(nfcCardID).getTotalPoints() == 0){
                    noRedeemAlert();
                    return;
                }
                else{
                    i.setClassName(getApplicationContext(), RedeemActivity.class.getName());
                    editor.putString("redeem_type", "mobile_nfc");
                    editor.commit();
                }
            }
            else {
                isTransaction = false;
                i.setClassName(getApplicationContext(), TransactionActivity.class.getName());
                editor.putString("trans_type", "mobile_nfc");
                editor.commit();
            }

            try{
                dialog.dismiss();
            }
            catch (Exception e){
                Log.d(getString(R.string.app_name), "Dialog exception!");
            }
            startActivity(i);
        }
        else{
            isTransaction = isRedeem = false;
        }
    }

    @Override
    protected void onResume(){
        super.onResume();

        SharedPreferences settings = this.getSharedPreferences(getString(R.string.preferences), 0);
        if(!settings.getBoolean("loggedIn", false)){
            startActivity(new Intent(this, SigninActivity.class));
        }

        if(adapter != null){
            //check if NFC is enabled
            boolean nfcEnabled = adapter.isEnabled();

            if(!nfcEnabled){
                Toast.makeText(getApplicationContext(), "Please activate NFC then press Back to return to the application!", Toast.LENGTH_LONG).show();
                startActivityForResult(new Intent(android.provider.Settings.ACTION_WIRELESS_SETTINGS), 0);
            }
        }
        nfcPendingIntent = PendingIntent.getActivity(this, 0, new Intent(this, getClass()).addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP), 0);

        //intent filter to handle NDEF NFC tags detected from within the application
        IntentFilter techDetected = new IntentFilter(NfcAdapter.ACTION_TECH_DISCOVERED);

        try{
            //try to catch all MIME types
            techDetected.addDataType("*/*");
        }
        catch (IntentFilter.MalformedMimeTypeException e) {
            throw new RuntimeException("could not add MIME type.", e);
        }

        readTagFilters = new IntentFilter[] {techDetected};

        mTechLists = new String[][] {
                new String[] {IsoDep.class.getName()},
                new String[] {NfcA.class.getName()},
                new String[] {NfcB.class.getName()},
                new String[] {NfcF.class.getName()},
                new String[] {NfcV.class.getName()},
                new String[] {Ndef.class.getName()},
                new String[] {NdefFormatable.class.getName()},
                new String[] {MifareClassic.class.getName()},
                new String[] {MifareUltralight.class.getName()}
        };

        //enable priority for current activity to detect scanned tags
        adapter.enableForegroundDispatch(this, nfcPendingIntent, readTagFilters, mTechLists);
    }

    @Override
    protected void onPause(){
        super.onPause();
        if(adapter != null)
            adapter.disableForegroundDispatch(this);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {

        Log.i(getString(R.string.app_name), "menu called");

        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.main, menu);
        syncItem = menu.findItem(R.id.sync_reminder);
        //if there are no transactions to be uploaded, gray out the button
        if(db.getTotalNumberOfUnsyncedTransactions() == 0 && db.getTotalNumberOfUnSyncedRedeems() == 0){
            syncItem.setVisible(false);
            Button btnActivity=(Button)findViewById(R.id.activityBtn);
            btnActivity.setEnabled(false);
            //findViewById(R.id.activityBtn).setVisibility(View.INVISIBLE);
            btnActivity.setBackground(getResources().getDrawable(R.drawable.menu_button_disabled));
            btnActivity.setTextColor(getResources().getColor(R.color.barter_grey));

            btnActivity.setCompoundDrawablesWithIntrinsicBounds(R.drawable.activity_icon_disabled, 0, 0, 0);
            //findViewById(R.id.activityBtn).setTextColor(Color.BLACK);
        }
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {

        switch (item.getItemId()){
            case R.id.sync_reminder:
                startActivity(new Intent(this, SyncActivity.class));
                break;
            case R.id.profile_menu:
                startActivity(new Intent(this, ProfileActivity.class));
                break;
            case R.id.stats_menu:
                startActivity(new Intent(this, StatsActivity.class));
                break;
            case R.id.manual_sync:
                manualSync();
                break;
            case R.id.support_menu:
                startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(getString(R.string.support_url))));
                break;
            case R.id.about_menu:
                startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(getString(R.string.about_url))));
                break;
            case R.id.sign_out_menu:
                logOut();
                break;
        }

        return super.onOptionsItemSelected(item);
    }

    public void noRedeemAlert(){
        final View dialogLayout = getLayoutInflater().inflate(R.layout.alert_dialog_notification, null);

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setView(dialogLayout);

        ((TextView) dialogLayout.findViewById(R.id.confirmDialogTitle)).setText(getString(R.string.no_redeem_points_title));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc)).setText(getString(R.string.no_redeem_points_desc1));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc2)).setText(getString(R.string.no_redeem_points_desc2));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogFooterDesc)).setText(getString(R.string.no_redeem_points_warning));

        (dialogLayout.findViewById(R.id.cancelBtn)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                dialog.dismiss();
            }
        });

        this.dialog = builder.create();
        this.dialog.show();
    }

    public void logOut(){

        final View dialogLayout = getLayoutInflater().inflate(R.layout.confirm_dialog, null);

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setView(dialogLayout);

        ((TextView) dialogLayout.findViewById(R.id.confirmDialogTitle)).setText(getString(R.string.logout_title));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc)).setText(getString(R.string.desc_one));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc2)).setText("");
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogFooterDesc)).setText(getString(R.string.desc_two));

        (dialogLayout.findViewById(R.id.cancelBtn)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                dialog.dismiss();
            }
        });

        (dialogLayout.findViewById(R.id.okBtn)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                getApplicationContext().getSharedPreferences(getString(R.string.preferences), 0).edit().clear().commit();
                DatabaseHandler db = new DatabaseHandler(getApplicationContext());
                db.deleteAllRecords();
                startActivity(new Intent(getApplicationContext(), SigninActivity.class).addFlags(Intent.FLAG_ACTIVITY_NO_HISTORY));
                dialog.cancel();
            }
        });

        this.dialog = builder.create();
        this.dialog.show();
    }

    public void redeemAlert(){

        isRedeem = true;

        final View dialogLayout = getLayoutInflater().inflate(R.layout.redeem_input_dialog, null);

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setView(dialogLayout);

        (dialogLayout.findViewById(R.id.button1)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                editor.putString("redeem_type", "mobile_qr");
                editor.commit();
                dialog.dismiss();
                IntentIntegrator scanIntegrator = new IntentIntegrator((Activity) view.getContext());
                scanIntegrator.initiateScan();
            }
        });
        (dialogLayout.findViewById(R.id.button2)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                editor.putString("redeem_type", "mobile_nfc");
                editor.commit();
                dialog.dismiss();
                interactionType = "redeem";
                scanCardDialog(interactionType);
            }
        });
        (dialogLayout.findViewById(R.id.button3)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                editor.putString("redeem_type", "mobile_manual");
                editor.commit();
                dialog.dismiss();
                rfidKeyboardAlert();
            }
        });

        this.dialog = builder.create();
        this.dialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
            @Override
            public void onCancel(DialogInterface dialogInterface) {
                isRedeem = false;
            }
        });
        this.dialog.show();
    }

    public void transactionAlert(){

        isTransaction = true;

        final View dialogLayout = getLayoutInflater().inflate(R.layout.transaction_input_dialog, null);

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setView(dialogLayout);

        (dialogLayout.findViewById(R.id.button1)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                editor.putString("trans_type", "mobile_qr");
                editor.commit();
                dialog.dismiss();
                IntentIntegrator scanIntegrator = new IntentIntegrator((Activity) view.getContext());
                scanIntegrator.initiateScan();
            }
        });
        (dialogLayout.findViewById(R.id.button2)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                editor.putString("trans_type", "mobile_nfc");
                editor.commit();
                dialog.dismiss();

                interactionType = "transaction";
                scanCardDialog(interactionType);
            }
        });
        (dialogLayout.findViewById(R.id.button3)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                editor.putString("trans_type", "mobile_manual");
                editor.commit();
                dialog.dismiss();
                rfidKeyboardAlert();
            }
        });

        this.dialog = builder.create();
        this.dialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
            @Override
            public void onCancel(DialogInterface dialogInterface) {
                isTransaction = false;
            }
        });
        this.dialog.show();
    }

    public void rfidKeyboardAlert(){

        final View dialogLayout = getLayoutInflater().inflate(R.layout.rfid_input_dialog, null);

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setView(dialogLayout);

        (dialogLayout.findViewById(R.id.okBtn)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String nfcCardID = ((TextView) dialogLayout.findViewById(R.id.editText)).getText().toString();
                Intent i = new Intent();
                i.putExtra("nfcCardID", nfcCardID);
                if(isRedeem){
                    isRedeem = false;
                    if(db.getConsumerData(nfcCardID).getTotalPoints() == 0){
                        noRedeemAlert();
                        return;
                    }
                    else{
                        i.setClassName(getApplicationContext(), RedeemActivity.class.getName());
                    }
                }
                else if(isTransaction){
                    isTransaction = false;
                    i.setClassName(getApplicationContext(), TransactionActivity.class.getName());
                }
                startActivity(i.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP));
                dialog.dismiss();
            }
        });

        this.dialog = builder.create();
        this.dialog.show();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        IntentResult scanningResult = IntentIntegrator.parseActivityResult(requestCode, resultCode, data);

        if (scanningResult != null) {
            if(scanningResult.getFormatName() != null){
                //we have a result
                String nfcCardID = scanningResult.getContents();
                Intent i = new Intent();
                i.putExtra("nfcCardID", nfcCardID);
                if(isRedeem){
                    isRedeem = false;

                    if(db.getConsumerData(nfcCardID).getTotalPoints() == 0){
                        noRedeemAlert();
                        return;
                    }
                    else{
                        i.setClassName(getApplicationContext(), RedeemActivity.class.getName());
                    }
                }
                else if(isTransaction){
                    isTransaction = false;
                    i.setClassName(getApplicationContext(), TransactionActivity.class.getName());
                }
                startActivity(i);
            }
            else{
                isRedeem = isTransaction = false;
                Toast.makeText(getApplicationContext(), "No scan data received!", Toast.LENGTH_SHORT).show();
            }
        }
    }

    private void checkTimeSinceLastUpdate() throws ParseException {

        SimpleDateFormat sdf = new SimpleDateFormat("dd-MM-yyyy HH:mm:ss");
        Long lastUpdate = sdf.parse(settings.getString("lastCheck","")).getTime();
        Long currentTime = new Date().getTime();

        Log.e("TIME", "Current time: " + currentTime + " Last: " + lastUpdate + " Delta: " + (currentTime-lastUpdate));

        if(currentTime < lastUpdate){
            //The user's own mobile clock is reporting erroneous time - alert the user to change time
            wrongTimePopup();
        }
        else{
            int deltaTime = (int)((currentTime - lastUpdate) / 1000 / 60 / 60);
            if(deltaTime >= timeToCheck){
                //more than 3 days have passed since the last update - inform user
                Log.e("TIME", "Current time: " + currentTime + " Last: " + lastUpdate + " Delta: " + deltaTime);
                autoSync();
            }
            else{
                Log.e("TIME", "No sync required...");
            }
        }
    }

    private void wrongTimePopup(){

        final View dialogLayout = getLayoutInflater().inflate(R.layout.confirm_dialog, null);

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setView(dialogLayout);

        ((TextView) dialogLayout.findViewById(R.id.confirmDialogTitle)).setText(getString(R.string.wrong_time_title));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc)).setText(getString(R.string.wrong_time_msg));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc2)).setText("");
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogFooterDesc)).setText("");

        (dialogLayout.findViewById(R.id.okBtn)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                dialog.dismiss();
            }
        });

        (dialogLayout.findViewById(R.id.okBtn)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(new Intent(android.provider.Settings.ACTION_DATE_SETTINGS));
                dialog.cancel();
            }
        });

        this.dialog = builder.create();
        this.dialog.show();
    }

    private void autoSync(){

        final View dialogLayout = getLayoutInflater().inflate(R.layout.confirm_dialog, null);

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setView(dialogLayout);

        ((TextView) dialogLayout.findViewById(R.id.confirmDialogTitle)).setText(getString(R.string.auto_sync_title));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc)).setText(getString(R.string.auto_sync_msg));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc2)).setText(getString(R.string.auto_sync_note));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogFooterDesc)).setText(getString(R.string.auto_sync_footer));

        Button cancelBtn = (Button) dialogLayout.findViewById(R.id.cancelBtn);
        cancelBtn.setText("NO");
        cancelBtn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                editor.putString("lastCheck", Utils.getCurrentDate());
                editor.commit();
                Log.e("TIME", settings.getString("lastCheck", "Error on time"));
                dialog.dismiss();
            }
        });

        Button okBtn = (Button) dialogLayout.findViewById(R.id.okBtn);
        okBtn.setText("YES");
        okBtn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                updateAllData();
                dialog.cancel();
            }
        });

        this.dialog = builder.create();
        this.dialog.show();
    }

    private void manualSync(){

        final View dialogLayout = getLayoutInflater().inflate(R.layout.confirm_dialog, null);

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setView(dialogLayout);

        ((TextView) dialogLayout.findViewById(R.id.confirmDialogTitle)).setText(getString(R.string.manual_sync_title));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc)).setText(getString(R.string.manual_sync_msg));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc2)).setText(getString(R.string.manual_sync_note));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogFooterDesc)).setText(getString(R.string.manual_sync_footer));

        Button cancelBtn = (Button) dialogLayout.findViewById(R.id.cancelBtn);
        cancelBtn.setText("Deny");
        cancelBtn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                dialog.dismiss();
            }
        });

        Button okBtn = (Button) dialogLayout.findViewById(R.id.okBtn);
        okBtn.setText("Accept");
        okBtn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                updateAllData();
                dialog.cancel();
            }
        });

        this.dialog = builder.create();
        this.dialog.show();
    }

    private void updateAllData(){
        //update the sync data //verify network connection
        if(Utils.checkConnectivity(this)){
            //differentiate between selected checkboxes and not selected

            uploadDialog = new ProgressDialog(MainActivity.this);
            uploadDialog.setMessage("Uploading...");
            uploadDialog.setIndeterminate(true);
            uploadDialog.setCancelable(false);

            allRedeems = db.getRedeems();
            transactions = db.getTransactions();

            if(allRedeems.size() > 0){
                uploadRedeems(allRedeems);
            }

            if(transactions.size() != 0){
                uploadDialog.show();
                uploadAllTransactions();
            }
            else{
                getSync();
            }
        }
    }

    private class SyncRedeem extends AsyncTask<String, Void, String> {

        protected StringBuilder sb;
        protected String result;
        protected InputStream is;

        @Override
        protected void onPreExecute() {}

        @Override
        protected String doInBackground(String... params) {

            try {
                HttpClient client = new DefaultHttpClient();
                HttpPost post = new HttpPost(params[0]);

                post.setHeader("Content-type", "application/json");
                StringEntity se = new StringEntity(params[1]);
                post.setEntity(se);
                //set the response
                HttpResponse response = client.execute(post);
                HttpEntity entity = response.getEntity();
                is = entity.getContent();
            }
            catch (Exception e) {
                e.printStackTrace();
                Log.i(getString(R.string.app_name), "Error connecting "+e.getMessage());
            }

            //handle the response
            try {
                BufferedReader reader = new BufferedReader(new InputStreamReader(is, "iso-8859-1"), 8);
                sb = new StringBuilder();
                sb.append(reader.readLine() + "\n");
                String line = "0";

                while ((line = reader.readLine()) != null) {
                    sb.append(line + "\n");
                }
                reader.close();
                is.close();
                result = sb.toString();
                return result;
            } catch (Exception e) {
                Log.e(getString(R.string.app_name), "Error converting result " + e.toString());
                return null;
            }
        }

        @Override
        protected void onPostExecute(String result) {

            try{
                JSONObject allData = new JSONObject(result);
                Boolean received = allData.getBoolean("received");
                if(received){
                    Log.i(getString(R.string.app_name), "All redeems have been successfully uploaded to the database.");

                    Redeem redeem;

                    for(Iterator<Redeem> it = allRedeems.iterator(); it.hasNext();){

                        redeem = it.next();
                        redeem.setSynced(true);

                        it.remove();
                        //update the current transaction into the database
                        db.updateRedeemStatus(redeem.getRedeemID());
                    }

                    ((TextView) findViewById(R.id.textView2)).setText("0");
                    uploadDialog.dismiss();
                    Toast.makeText(getApplicationContext(), "All redeems have been successfully uploaded to the database.", Toast.LENGTH_SHORT).show();
                }
                else{
                    Log.i(getString(R.string.app_name), "There was a problem while saving your request!" + allData.getJSONArray("notEntered"));
                    uploadDialog.dismiss();
                    Toast.makeText(getApplicationContext(), "There seems to be an error while uploading your request. Please try again later.", Toast.LENGTH_LONG).show();
                }
            }
            catch (Exception e) {
                uploadDialog.dismiss();
                Log.e(getString(R.string.app_name), "Yoo: " + e.getMessage());
            }
        }
    }

    public void uploadRedeems(ArrayList<Redeem> allRedeems){
        //create an array of json object of type transaction
        JSONArray jsonArrayRedeems = new JSONArray();

        for(Redeem redeem: allRedeems){

            //create a JSONObject for every transaction
            JSONObject redeemJson = new JSONObject();
            try {
                redeemJson.put("trader_id", settings.getString("cardId",null));
                redeemJson.put("redeem_id", redeem.getRedeemID());
                redeemJson.put("consumer_id", redeem.getConsumerRFID());
                redeemJson.put("redeem_type", redeem.getRedeemType());
                redeemJson.put("points_deducted", redeem.getPointsDeducted());
                redeemJson.put("redeem_timestamp", redeem.getTimestamp());

            } catch (Exception e) {
                Log.e(getString(R.string.app_name), "JSON exception from redeem data.");
            }

            Log.i(getString(R.string.app_name), "Type: " + redeem.getRedeemType());

            jsonArrayRedeems.put(redeemJson);
        }

        //convert the JSON to string
        String dataToSend = jsonArrayRedeems.toString();

        //create the parameters to be passed to the network handler
        params = new String[2];
        params[0] = getString(R.string.redeem_url);
        params[1] = dataToSend;

        //send the consumers' data to the server
        SyncRedeem syncRedeem = new SyncRedeem();
        syncRedeem.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }

    public void uploadAllTransactions(){
        //create an array of json object of type transaction
        JSONArray jsonArrayTransaction = new JSONArray();

        for(Transaction transaction : transactions){

            //create a JSONObject for every transaction
            JSONObject transactionJson = new JSONObject();
            try {
                transactionJson.put("trader_id", settings.getString("cardId", null));
                transactionJson.put("trans_id", transaction.getTransactionID());
                transactionJson.put("consumer_id", transaction.getConsumerID());
                transactionJson.put("trans_lat", transaction.getLatitude());
                transactionJson.put("trans_lon", transaction.getLongitude());
                transactionJson.put("trans_type", transaction.getType());
                transactionJson.put("trans_origin", transaction.getOrigin());
                transactionJson.put("trans_price", transaction.getPrice());
                transactionJson.put("trans_points", transaction.getPoints());
                transactionJson.put("trans_time", transaction.getTimestamp());
            } catch (Exception e) {
                Log.e(getString(R.string.app_name), "JSON exception from consumer's data");
            }

            jsonArrayTransaction.put(transactionJson);
        }

        //convert the JSON to string
        String dataToSend = jsonArrayTransaction.toString();

        //create the parameters to be passed to the network handler
        params = new String[2];
        params[0] = getString(R.string.auto_manual_sync_url);
        params[1] = dataToSend;

        //send the consumers' data to the server
        SyncTransaction syncTransaction = new SyncTransaction();
        syncTransaction.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }

    private class SyncTransaction extends AsyncTask<String, Void, String> {

        protected StringBuilder sb;
        protected String result;
        protected InputStream is;

        @Override
        protected void onPreExecute() {}

        @Override
        protected String doInBackground(String... params) {

            try {
                HttpClient client = new DefaultHttpClient();
                HttpPost post = new HttpPost(params[0]);

                post.setHeader("Content-type", "application/json");
                StringEntity se = new StringEntity(params[1]);
                post.setEntity(se);
                //set the response
                HttpResponse response = client.execute(post);
                HttpEntity entity = response.getEntity();
                is = entity.getContent();
            }
            catch (Exception e) {
                e.printStackTrace();
                Log.i(getString(R.string.app_name), "Error connecting "+e.getMessage());
            }

            //handle the response
            try {
                BufferedReader reader = new BufferedReader(new InputStreamReader(is, "iso-8859-1"), 8);
                sb = new StringBuilder();
                sb.append(reader.readLine() + "\n");
                String line = "0";

                while ((line = reader.readLine()) != null) {
                    sb.append(line + "\n");
                }
                reader.close();
                is.close();
                result = sb.toString();
                return result;
            } catch (Exception e) {
                Log.e(getString(R.string.app_name), "Error converting result " + e.toString());
                return null;
            }
        }

        @Override
        protected void onPostExecute(String result) {

            try{
                JSONObject allData = new JSONObject(result);
                Boolean received = allData.getBoolean("received");

                if(received){
                    Log.i(getString(R.string.app_name), "All transactions have been successfully uploaded to the database");

                    for(Iterator<Transaction> it = transactions.iterator(); it.hasNext();){
                        Transaction transaction = it.next();
                        //remove the current transaction from the transaction ArrayList
                        it.remove();
                        //update the current transaction into the database
                        db.updateTransactionStatus(transaction.getTransactionID());
                    }

                    JSONArray consumersToBeUpdated = allData.getJSONArray("customerData");
                    JSONObject traderStats = allData.getJSONObject("traderTotals");

                    //parse the sync data
                    for(int i = 0; i < consumersToBeUpdated.length(); i++){
                        JSONObject consumer = consumersToBeUpdated.getJSONObject(i);
                        db.overrideConsumerStats(consumer.getString("customer_id"), consumer.getInt("customer_spend"), consumer.getInt("customer_points"), consumer.getInt("customer_occurrences"), consumer.getString("timestamp"));
                    }

                    updateTraderStats(traderStats);

                    uploadDialog.dismiss();
                    Toast.makeText(getApplicationContext(), "All transactions have been successfully uploaded.", Toast.LENGTH_LONG).show();
                }
                else{
                    Log.i(getString(R.string.app_name), "There was a problem while uploading the current transaction" + allData.getJSONArray("notEntered"));
                    uploadDialog.dismiss();
                    Toast.makeText(getApplicationContext(), "There seems to be an error while uploading your transactions. Please try again later.", Toast.LENGTH_LONG).show();
                }
            }
            catch (Exception e) {
                uploadDialog.dismiss();
                Log.e(getString(R.string.app_name), e.getMessage());
            }
        }
    }

    public void getSync(){

        //create a JSONObject for every transaction
        JSONObject traderJson = new JSONObject();
        try {
            traderJson.put("trader_id", settings.getString("cardId", null));
        } catch (Exception e) {
            Log.e(getString(R.string.app_name), "JSON exception from consumer's data");
        }

        //convert the JSON to string
        String dataToSend = traderJson.toString();

        //create the parameters to be passed to the network handler
        params = new String[2];
        params[0] = getString(R.string.get_sync_data);
        params[1] = dataToSend;

        //send the consumers' data to the server
        SyncConsumersTotal syncConsumer = new SyncConsumersTotal();
        syncConsumer.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }

    private class SyncConsumersTotal extends AsyncTask<String, Void, String> {

        protected StringBuilder sb;
        protected String result;
        protected InputStream is;

        @Override
        protected void onPreExecute() {}

        @Override
        protected String doInBackground(String... params) {

            try {
                HttpClient client = new DefaultHttpClient();
                HttpPost post = new HttpPost(params[0]);

                post.setHeader("Content-type", "application/json");
                StringEntity se = new StringEntity(params[1]);
                post.setEntity(se);
                //set the response
                HttpResponse response = client.execute(post);
                HttpEntity entity = response.getEntity();
                is = entity.getContent();
            }
            catch (Exception e) {
                e.printStackTrace();
                Log.i(getString(R.string.app_name), "Error connecting "+e.getMessage());
            }

            //handle the response
            try {
                BufferedReader reader = new BufferedReader(new InputStreamReader(is, "iso-8859-1"), 8);
                sb = new StringBuilder();
                sb.append(reader.readLine() + "\n");
                String line = "0";

                while ((line = reader.readLine()) != null) {
                    sb.append(line + "\n");
                }
                reader.close();
                is.close();
                result = sb.toString();
                return result;
            } catch (Exception e) {
                Log.e(getString(R.string.app_name), "Error converting result " + e.toString());
                return null;
            }
        }

        @Override
        protected void onPostExecute(String result) {

            try{
                JSONObject allData = new JSONObject(result);
                Boolean received = allData.getBoolean("received");

                if(received){
                    Log.i(getString(R.string.app_name), "All transactions have been successfully uploaded to the database");

                    JSONArray consumersToBeUpdated = allData.getJSONArray("customerData");
                    JSONObject traderStats = allData.getJSONObject("traderTotals");

                    for(int i = 0; i < consumersToBeUpdated.length(); i++){
                        JSONObject consumer = consumersToBeUpdated.getJSONObject(i);
                        db.overrideConsumerStats(consumer.getString("customer_id"), consumer.getInt("customer_spend"), consumer.getInt("customer_points"), consumer.getInt("customer_occurrences"), consumer.getString("timestamp"));
                    }

                    updateTraderStats(traderStats);

                    uploadDialog.dismiss();
                    Toast.makeText(getApplicationContext(), "All data has been successfully synced.", Toast.LENGTH_LONG).show();
                }
                else{
                    Log.i(getString(R.string.app_name), "There was a problem during the sync operation." + allData.getJSONArray("notEntered"));
                    uploadDialog.dismiss();
                    Toast.makeText(getApplicationContext(), "There was a problem during the sync operation. Please try again later.", Toast.LENGTH_LONG).show();
                }
            }
            catch (Exception e) {
                uploadDialog.dismiss();
                Log.e(getString(R.string.app_name), e.getMessage());
            }
        }
    }

    private void updateTraderStats(JSONObject traderStats) throws JSONException, ParseException {

        //editor.putString("totalTrans", traderStats.getString("total_trans"));
        editor.putInt("totalMobileTransactions", traderStats.getInt("total_mobile_trans"));
        editor.putInt("totalWebTransactions", traderStats.getInt("total_web_trans"));

        editor.putInt("totalNonBarterTransactions", traderStats.getInt("total_non_barter_trans"));
        editor.putInt("totalNonLocalTransactions", traderStats.getInt("total_non_local_trans"));

        editor.putString("totalPriceGoods", traderStats.getString("total_price_goods"));
        editor.putString("totalPriceServices", traderStats.getString("total_price_services"));
        editor.putString("totalPriceBoth", traderStats.getString("total_price_both"));
        editor.putString("lastUploaded", Utils.convertServerTimestamp(traderStats.getString("last_uploaded")));
        editor.putString("lastCheck", Utils.convertServerTimestamp(traderStats.getString("last_uploaded")));
        editor.commit();

        //reset the options menu
        invalidateOptionsMenu();
    }
}