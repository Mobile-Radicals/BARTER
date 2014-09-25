package com.trade.barter;

import android.app.ActionBar;
import android.app.Activity;
import android.app.PendingIntent;
import android.app.ProgressDialog;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.graphics.drawable.ColorDrawable;
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
import android.view.View;
import android.view.WindowManager;
import android.widget.NumberPicker;
import android.widget.TextView;
import android.widget.Toast;

import com.trade.barter.utils.ConsumerData;
import com.trade.barter.utils.DatabaseHandler;
import com.trade.barter.utils.Redeem;
import com.trade.barter.utils.Utils;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;

public class RedeemActivity extends Activity {

    private NfcAdapter adapter;
    private PendingIntent nfcPendingIntent;
    private IntentFilter[] readTagFilters;
    private String[][] mTechLists;
    private ActionBar actionBar;
    private String nfcCardID;

    private DatabaseHandler db;
    private SharedPreferences settings;
    private String[] params;
    private Redeem redeem;
    private ProgressDialog dialog;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.redeem_main);

        adapter = NfcAdapter.getDefaultAdapter(this);

        //stop the soft keyboard from popping up
        this.getWindow().setSoftInputMode(WindowManager.LayoutParams.SOFT_INPUT_STATE_ALWAYS_HIDDEN);

        actionBar = this.getActionBar();
        nfcCardID = getIntent().getExtras().getString("nfcCardID");
        actionBar.setTitle(nfcCardID);
        actionBar.setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.pale_yellow)));
        actionBar.setDisplayHomeAsUpEnabled(true);

        //check if NFC is enabled
        boolean nfcEnabled = adapter.isEnabled();
        if(!nfcEnabled){
            Toast.makeText(getApplicationContext(), "Please activate NFC then press Back to return to the application!", Toast.LENGTH_LONG).show();
            startActivityForResult(new Intent(android.provider.Settings.ACTION_WIRELESS_SETTINGS), 0);
        }

        //instantiate the database
        db = new DatabaseHandler(this.getApplicationContext());
        //get the current shared preferences
        settings = this.getSharedPreferences(getString(R.string.preferences), 0);

        //display the current consumer data
        final ConsumerData consumerData = db.getConsumerData(nfcCardID);
        ((TextView) findViewById(R.id.pts)).setText(String.valueOf(consumerData.getTotalPoints()));
        ((TextView) findViewById(R.id.spend)).setText(Utils.convertPrice(consumerData.getTotalSpent()));
        ((TextView) findViewById(R.id.trans)).setText(String.valueOf(consumerData.getTotalTransactions()));

        final NumberPicker picker = (NumberPicker) findViewById(R.id.numberPicker);
        picker.setMinValue(0);
        picker.setMaxValue(consumerData.getTotalPoints());

        findViewById(R.id.recordRedeemBtn).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {

                //get the current number of points the trader wants to deduce from the current consumer
                int pointsDeducted = picker.getValue();
                //Log.d(getString(R.string.app_name), "Points: " + pointsDeducted);

                //try and upload the data to the server
                redeem = new Redeem(settings.getString("cardId", null), nfcCardID, settings.getString("redeem_type", null), pointsDeducted, Utils.getCurrentDate(), false);
                db.addRedeem(redeem);
                db.updateConsumerData(nfcCardID, pointsDeducted);

//                //verify network connection
//                if(Utils.checkConnectivity(view.getContext())){
//                    dialog = new ProgressDialog(RedeemActivity.this);
//                    dialog.setMessage("Sending...");
//                    dialog.setIndeterminate(true);
//                    dialog.setCancelable(false);
//                    dialog.show();
//                    //try to upload the redeemed points to the db
//                    uploadRedeem(redeem);
//                }
//                else{
//                    Toast.makeText(view.getContext(), "Your option was successfully saved.", Toast.LENGTH_LONG).show();
//                    finish();
//                }

                //inform the user and return to the main menu
                Toast.makeText(view.getContext(), "Your option was successfully saved.", Toast.LENGTH_LONG).show();
                startActivity(new Intent(getApplicationContext(), MainActivity.class).addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP));
                finish();
            }
        });
    }

    public void uploadRedeem(Redeem redeem){

        //create an array of json object of type transaction
        JSONArray jsonArrayRedeem = new JSONArray();

        //create a JSONObject for every transaction
        JSONObject redeemJson = new JSONObject();
        try {
            redeemJson.put("trader_id", settings.getInt("id", 0));
            redeemJson.put("redeem_id", redeem.getRedeemID());
            redeemJson.put("consumer_id", redeem.getConsumerRFID());
            redeemJson.put("points_deducted", redeem.getPointsDeducted());
            redeemJson.put("redeem_timestamp", redeem.getTimestamp());
        } catch (Exception e) {
            Log.e(getString(R.string.app_name), "JSON exception from redeem data.");
        }

        jsonArrayRedeem.put(redeemJson);

        //convert the JSON to string
        String dataToSend = jsonArrayRedeem.toString();

        //create the parameters to be passed to the network handler
        params = new String[3];
        params[0] = getString(R.string.redeem_url);
        params[1] = dataToSend;

        //send the consumers' data to the server
        SyncRedeem syncRedeem = new SyncRedeem();
        syncRedeem.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
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
                    Log.i(getString(R.string.app_name), "Redeem has been successfully added to the database!");

                    //update the current redeem into the database
                    db.updateRedeemStatus(redeem.getRedeemID());

                    dialog.dismiss();
                    Toast.makeText(getApplicationContext(), "Your option was saved!.", Toast.LENGTH_LONG).show();

                }
                else{
                    Log.i(getString(R.string.app_name), "There was a problem while saving your request!" + allData.getJSONArray("notEntered"));
                    dialog.dismiss();
                    Toast.makeText(getApplicationContext(), "There seems to be an error while uploading your request. Please try again later.", Toast.LENGTH_LONG).show();
                }
                finish();
            }
            catch (Exception e) {
                dialog.dismiss();
                Log.e(getString(R.string.app_name), e.getMessage());
            }
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
}
