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
}
