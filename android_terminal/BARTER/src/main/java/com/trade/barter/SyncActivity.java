package com.trade.barter;

import android.app.ListActivity;
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
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;

import com.trade.barter.utils.DatabaseHandler;
import com.trade.barter.utils.Redeem;
import com.trade.barter.utils.Transaction;
import com.trade.barter.utils.TransactionAdapter;
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
import java.util.ArrayList;
import java.util.Iterator;

public class SyncActivity extends ListActivity {

    private DatabaseHandler db;
    private TransactionAdapter transactionAdapter;
    private ArrayList<Transaction> transactions;
    private ArrayList<Redeem> allRedeems;

    private String[] params;
    private SharedPreferences settings;

    private ArrayList<Integer> transactionsPositions;
    private Transaction transaction;
    private ProgressDialog dialog;

    private NfcAdapter adapter;
    private PendingIntent nfcPendingIntent;
    private IntentFilter[] readTagFilters;
    private String[][] mTechLists;

    private boolean noTransactions = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.sync_main);

        this.getActionBar().setDisplayHomeAsUpEnabled(true);
        this.getActionBar().setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.pale_red)));

        //get the current shared preferences
        settings = this.getSharedPreferences(getString(R.string.preferences), 0);

        //declare an NFC adapter
        adapter = NfcAdapter.getDefaultAdapter(this);
    }

    @Override
    protected void onNewIntent(Intent intent){
        Log.d(getString(R.string.app_name), "NFC intent was discovered");
    }

    @Override
    protected void onPause(){
        super.onPause();
        if(adapter != null)
            adapter.disableForegroundDispatch(this);
    }

    @Override
    protected void onListItemClick(ListView l, View v, int position, long id) {
        Object clickedField = l.getItemAtPosition(position);
        Intent i = new Intent(this, ModifyTransactionActivity.class);
        i.putExtra("transaction", (Transaction) clickedField);
        startActivity(i);
    }

    @Override
    protected void onResume() {
        super.onResume();

        db = new DatabaseHandler(this.getApplicationContext());
        transactions = db.getTransactions();
        transactionAdapter = new TransactionAdapter(this, transactions);
        setListAdapter(transactionAdapter);
        //get all the redeem transactions
        allRedeems = db.getRedeems();

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
        displayRedeems();
    }

    private void displayRedeems(){
        if(allRedeems.size() > 0){
            findViewById(R.id.relativeLayout).setVisibility(View.VISIBLE);
            ((TextView) findViewById(R.id.textView2)).setText(String.valueOf(allRedeems.size()));
        }
        else{
            findViewById(R.id.relativeLayout).setVisibility(View.GONE);
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.sync, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()){
            case R.id.upload:

                //verify network connection
                if(Utils.checkConnectivity(this)){
                    //differentiate between selected checkboxes and not selected

                    dialog = new ProgressDialog(SyncActivity.this);
                    dialog.setMessage("Uploading...");
                    dialog.setIndeterminate(true);
                    dialog.setCancelable(false);

                    //jsonArrayTransaction = new JSONArray();
                    transactionsPositions = new ArrayList<Integer>();

                    //determine how many checked transactions are there
                    for(Iterator<Transaction> it = transactions.iterator(); it.hasNext();){
                        transaction = it.next();
                        if(transaction.isCheckboxChecked()){
                            //add the if to the array
                            transactionsPositions.add(transactions.indexOf(transaction));
                        }
                    }

                    if(allRedeems.size() > 0){
                        uploadRedeems(allRedeems);
                    }

                    if(transactionsPositions.size() != 0){
                        dialog.show();
                        uploadTransactions(transactionsPositions);
                    }
                    else{
                        if(transactions.size() != 0){
                            dialog.show();
                            uploadAllTransactions();
                        }
                        else{
                            noTransactions = true;
                            //Toast.makeText(getApplicationContext(), "There are no transactions to be uploaded!", Toast.LENGTH_LONG).show();
                        }
                    }
                }
            break;
        }
        return super.onOptionsItemSelected(item);
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
                    dialog.dismiss();
                    Toast.makeText(getApplicationContext(), "All redeems have been successfully uploaded to the database.", Toast.LENGTH_SHORT).show();
                    if(noTransactions) {
                        startActivity(new Intent(getApplicationContext(), MainActivity.class).addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP));
                        finish();
                    }
                }
                else{
                    Log.i(getString(R.string.app_name), "There was a problem while saving your request!" + allData.getJSONArray("notEntered"));
                    dialog.dismiss();
                    Toast.makeText(getApplicationContext(), "There seems to be an error while uploading your request. Please try again later.", Toast.LENGTH_LONG).show();
                }
            }
            catch (Exception e) {
                dialog.dismiss();
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
        params = new String[3];
        params[0] = getString(R.string.redeem_url);
        params[1] = dataToSend;

        //send the consumers' data to the server
        SyncRedeem syncRedeem = new SyncRedeem();
        syncRedeem.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }

    public void uploadTransactions(ArrayList<Integer> transactionsPositions){

        //create an array of json object of type transaction
        JSONArray jsonArrayTransaction = new JSONArray();

        for(Integer transPosition : transactionsPositions){
            //create a new transaction based on the current position
            Transaction transaction = transactions.get(transPosition);

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
                Log.e(getString(R.string.app_name), "JSON exception from transaction data.");
            }

            jsonArrayTransaction.put(transactionJson);
        }

        //convert the JSON to string
        String dataToSend = jsonArrayTransaction.toString();

        //create the parameters to be passed to the network handler
        params = new String[3];
        params[0] = getString(R.string.upload_url);
        params[1] = dataToSend;
        params[2] = "selected";

        //send the consumers' data to the server
        SyncTransaction syncTransaction = new SyncTransaction();
        syncTransaction.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
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
        params = new String[3];
        params[0] = getString(R.string.upload_url);
        params[1] = dataToSend;
        params[2] = "all";

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
                    startActivity(new Intent(getApplicationContext(), MainActivity.class).addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP));
                    finish();

                    if(params[2].equals("selected")){
                        Transaction transaction;

                        for(Iterator<Transaction> it = transactions.iterator(); it.hasNext();){

                            transaction = it.next();
                            if(transaction.isCheckboxChecked()){
                                transaction.setCheckboxChecked(false);

                                //remove the current transaction from the transaction ArrayList
                                it.remove();
                                //update the current transaction into the database
                                db.updateTransactionStatus(transaction.getTransactionID());
                                updateTraderStats(transaction);
                            }
                        }
                    }
                    else if(params[2].equals("all")){
                        Transaction transaction;

                        for(Iterator<Transaction> it = transactions.iterator(); it.hasNext();){
                            transaction = it.next();
                            //remove the current transaction from the transaction ArrayList
                            it.remove();
                            //update the current transaction into the database
                            db.updateTransactionStatus(transaction.getTransactionID());
                            updateTraderStats(transaction);
                        }
                    }

                    transactionAdapter.notifyDataSetChanged();

                    dialog.dismiss();
                    Toast.makeText(getApplicationContext(), "All selected transactions have been successfully uploaded.", Toast.LENGTH_LONG).show();

                }
                else{
                    Log.i(getString(R.string.app_name), "There was a problem while uploading the current transaction" + allData.getJSONArray("notEntered"));
                    dialog.dismiss();
                    Toast.makeText(getApplicationContext(), "There seems to be an error while uploading your transactions. Please try again later.", Toast.LENGTH_LONG).show();
                }
            }
            catch (Exception e) {
                dialog.dismiss();
                Log.e(getString(R.string.app_name), e.getMessage());
            }
        }
    }

    private void updateTraderStats(Transaction transaction){
        SharedPreferences.Editor editor = settings.edit();

        //update all trader's stats
        editor.putInt("totalMobileTransactions", (settings.getInt("totalMobileTransactions", 0)+ 1));

        if(transaction.getType().equals("goods")){
            editor.putString("totalPriceGoods", String.valueOf(transaction.getPrice() + Double.parseDouble(settings.getString("totalPriceGoods", ""))));
        }
        else if(transaction.getType().equals("services")){
            editor.putString("totalPriceServices", String.valueOf(transaction.getPrice() + Double.parseDouble(settings.getString("totalPriceServices", ""))));
        }
        else if(transaction.getType().equals("both")){
            editor.putString("totalPriceBoth", String.valueOf(transaction.getPrice() + Double.parseDouble(settings.getString("totalPriceBoth", ""))));
        }
        editor.commit();
    }
}
