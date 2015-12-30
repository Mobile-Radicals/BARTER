package com.trade.barter;

import android.app.ActionBar;
import android.app.Activity;
import android.app.PendingIntent;
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
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.view.WindowManager;
import android.widget.EditText;
import android.widget.RatingBar;
import android.widget.TextView;
import android.widget.Toast;
import android.widget.ToggleButton;

import com.trade.barter.utils.ConsumerData;
import com.trade.barter.utils.DatabaseHandler;
import com.trade.barter.utils.Transaction;
import com.trade.barter.utils.Utils;

public class ModifyTransactionActivity extends Activity {

    private ActionBar actionBar;
    private String nfcCardID;

    private Transaction transaction;
    private DatabaseHandler db;

    private NfcAdapter adapter;
    private PendingIntent nfcPendingIntent;
    private IntentFilter[] readTagFilters;
    private String[][] mTechLists;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.transaction_main);

        adapter = NfcAdapter.getDefaultAdapter(this);
        nfcPendingIntent = PendingIntent.getActivity(this, 0, new Intent(this, getClass()).addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP), 0);

        actionBar = this.getActionBar();
        transaction = (Transaction) getIntent().getSerializableExtra("transaction");

        //stop the soft keyboard from popping up
        this.getWindow().setSoftInputMode(WindowManager.LayoutParams.SOFT_INPUT_STATE_ALWAYS_HIDDEN);

        nfcCardID = transaction.getConsumerID();
        actionBar.setTitle(nfcCardID);
        actionBar.setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.barter_green)));
        actionBar.setDisplayHomeAsUpEnabled(true);

        //instantiate the database
        db = new DatabaseHandler(this.getApplicationContext());

        //declare an NFC adapter
        adapter = NfcAdapter.getDefaultAdapter(this);

        //display the current consumer data
        ConsumerData consumerData = db.getConsumerData(nfcCardID);
        ((TextView) findViewById(R.id.loyaltyValue)).setText(String.valueOf(consumerData.getTotalPoints()));
        ((TextView) findViewById(R.id.numberValue)).setText(String.valueOf(consumerData.getTotalTransactions()));
        ((TextView) findViewById(R.id.spendValue)).setText(Utils.convertPrice(consumerData.getTotalSpent()));

        //set the type of the trader based on their online settings
        String businessProduce = transaction.getType();

        //set the business operation of the current trader based on the shared pref settings
        if(businessProduce != null){
            if(businessProduce.equals("goods")){
                //trader activity has been set to goods
                ((ToggleButton) findViewById(R.id.goodsToggle)).setChecked(true);
            }
            if(businessProduce.equals("services")){
                //trader activity has been set to goods
                ((ToggleButton) findViewById(R.id.servicesToggle)).setChecked(true);
            }
            if(businessProduce.equals("both")){
                //trader activity has been set to services
                ((ToggleButton) findViewById(R.id.goodsToggle)).setChecked(true);
                ((ToggleButton) findViewById(R.id.servicesToggle)).setChecked(true);
            }
        }

        ((RatingBar) findViewById(R.id.transactionRatingBar)).setRating(transaction.getPoints());
        ((TextView) findViewById(R.id.transactionValue)).setText(Utils.convertPrice(transaction.getPrice()));

        findViewById(R.id.recordTransactionBtn).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                //get the data to be saved in the database
                int points = (int)((RatingBar) findViewById(R.id.transactionRatingBar)).getRating();

                String priceString = ((EditText) findViewById(R.id.transactionValue)).getText().toString();
                double price = 0.00;
                if(priceString.length() > 0){
                    price = Utils.convertPrice(priceString);
                }

                ToggleButton goodsBtn = (ToggleButton) findViewById(R.id.goodsToggle);
                ToggleButton servicesBtn = (ToggleButton) findViewById(R.id.servicesToggle);

                String type;
                if(goodsBtn.isChecked() && !servicesBtn.isChecked()){
                    type = "goods";
                }
                else if (!goodsBtn.isChecked() && servicesBtn.isChecked()){
                    type = "services";
                }
                else if(goodsBtn.isChecked() && servicesBtn.isChecked()){
                    type = "both";
                }
                else{
                    Toast.makeText(view.getContext(), "Please check your primary business type.", Toast.LENGTH_LONG).show();
                    return;
                }

                db.overrideTransaction(new Transaction(transaction.getTransactionID(), nfcCardID, type, price, points));
                db.updateConsumerData(nfcCardID, transaction.getPrice(), transaction.getPoints(), price, points);

                Toast.makeText(view.getContext(), "Your transaction has been successfully updated.", Toast.LENGTH_SHORT).show();

                finish();

            }
        });
    }


    @Override
    protected void onNewIntent(Intent intent){
        Log.d(getString(R.string.app_name), "NFC intent was discovered");
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
