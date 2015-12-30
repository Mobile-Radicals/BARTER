package com.trade.barter;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Typeface;
import android.graphics.drawable.ColorDrawable;
import android.net.Uri;
import android.nfc.NfcAdapter;
import android.os.Bundle;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import com.trade.barter.api.ApiDoohickey;
import com.trade.barter.utils.DatabaseHandler;
import com.trade.barter.utils.Redeem;
import com.trade.barter.utils.Transaction;
import com.trade.barter.utils.Utils;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;

import google.zxing.integration.android.IntentIntegrator;
import google.zxing.integration.android.IntentResult;

public class MainActivity extends Activity {

    private NfcAdapter adapter;

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
    private int timeToCheck = 72;
    private static MainActivity instance;
    
    public static Context getContext()
    {
    	return instance.getApplicationContext();
    }
    
    public static MainActivity getInstance()
    {
    	return instance;
    }
    
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        instance=this;
        setContentView(R.layout.activity_main);

        db = new DatabaseHandler(this.getApplicationContext());
        adapter = NfcAdapter.getDefaultAdapter(this);
        Typeface font = Typeface.createFromAsset(getAssets(), "square.ttf");
        settings = this.getSharedPreferences(getString(R.string.preferences), 0);
        editor = settings.edit();
        
        NFCState.check(this);       

        Button syncButton = createButton(R.id.activityBtn,font,switchActivityListener(SyncActivity.class));
        Button profileButton = createButton(R.id.profileBtn,font,switchActivityListener(ProfileActivity.class));
        Button transactionButton = createButton(R.id.transactionBtn,font,new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                transactionAlert();
            }
        });
        Button loyaltyButton = createButton(R.id.redeemBtn,font,new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                redeemAlert();
            }
        });

        getActionBar().setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.charcole_gray)));

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


    private Button createButton(int id, Typeface font, View.OnClickListener onClickListener)
    {
        Button button = (Button) findViewById(id);
        button.setTypeface(font);
        button.setOnClickListener(onClickListener);
        return button;
    }

    private View.OnClickListener switchActivityListener(final Class<? extends Activity> clazz)
    {
        return new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(new Intent(getApplicationContext(), clazz));
            }
        };
    }

    public void scanCardDialog(String interactionType){

        AlertDialog.Builder dialog = new AlertDialog.Builder(this);
     
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
            Intent newIntent = new Intent();
            newIntent.putExtra("nfcCardID", nfcCardID);

            if(isRedeem){
                isRedeem = false;

                if(db.getConsumerData(nfcCardID).getTotalPoints() == 0){
                    noRedeemAlert();
                    return;
                }
                else{
                    newIntent.setClassName(getApplicationContext(), RedeemActivity.class.getName());
                    editor.putString("redeem_type", "mobile_nfc");
                    editor.commit();
                }
            }
            else {
                isTransaction = false;
                newIntent.setClassName(getApplicationContext(), TransactionActivity.class.getName());
                editor.putString("trans_type", "mobile_nfc");
                editor.commit();
            }

            try{
                dialog.dismiss();
            }
            catch (Exception e){
                Log.d(getString(R.string.app_name), "Dialog exception! "+e.toString());
            }
            startActivity(newIntent);
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

        if(adapter != null)
            NFCState.check(this);
       
        NFCState.givePriority(this);
    }

    @Override
    protected void onPause(){
        super.onPause();
        NFCState.onPause(this);
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
            btnActivity.setBackground(getResources().getDrawable(R.drawable.menu_button_disabled));
            btnActivity.setTextColor(getResources().getColor(R.color.barter_grey));
            btnActivity.setCompoundDrawablesWithIntrinsicBounds(R.drawable.activity_icon_disabled, 0, 0, 0);
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
            	ApiDoohickey.getInstance().uploadRedeems(uploadDialog,allRedeems,false,null);
            }

            if(transactions.size() != 0){
                uploadDialog.show();
                ApiDoohickey.getInstance().uploadAllTransactions(uploadDialog,transactions);
            }
            else{
                ApiDoohickey.getInstance().getSync(uploadDialog);
            }
        }
    }

}