package com.trade.barter;

import android.app.Activity;
import android.app.PendingIntent;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
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
import android.widget.TextView;
import android.widget.Toast;

import com.trade.barter.utils.DatabaseHandler;
import com.trade.barter.utils.Utils;

public class StatsActivity extends Activity {

    private DatabaseHandler db;
    private NfcAdapter adapter;
    private PendingIntent nfcPendingIntent;
    private IntentFilter[] readTagFilters;
    private String[][] mTechLists;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.stats_main);

        this.getActionBar().setDisplayHomeAsUpEnabled(true);

        db = new DatabaseHandler(this);
        SharedPreferences settings = this.getSharedPreferences(getString(R.string.preferences), 0);

        //get the values from the shared pref and add them together with the current ones
        int totalNUmberOfMobileTransactions = settings.getInt("totalMobileTransactions", 0);
        int totalNUmberOfWebTransactions = settings.getInt("totalWebTransactions", 0);
        int totalNumberOfTransactions = totalNUmberOfMobileTransactions + totalNUmberOfWebTransactions;

        double totalValueOfGoods = Utils.convertPrice(settings.getString("totalPriceGoods", null));
        double totalValueOfServices = Utils.convertPrice(settings.getString("totalPriceServices", null));
        double totalValueOfGoodsAndServices = Utils.convertPrice(settings.getString("totalPriceBoth", null));

        //set total for mobile transactions
        ((TextView) findViewById(R.id.statsMobileTotalTransaction)).setText(String.valueOf(totalNUmberOfMobileTransactions));
        //set total for web transactions
        ((TextView) findViewById(R.id.statsWebTotalTransactions)).setText(String.valueOf(totalNUmberOfWebTransactions));
        //set total for all transactions
        ((TextView) findViewById(R.id.statsTotalTransactions)).setText(String.valueOf(totalNumberOfTransactions));

        //set the goods/services/both
        ((TextView) findViewById(R.id.statsGoodsValue)).setText(String.valueOf(totalValueOfGoods));
        ((TextView) findViewById(R.id.statsServicesValue)).setText(String.valueOf(totalValueOfServices));
        ((TextView) findViewById(R.id.statsGoodsServicesValue)).setText(String.valueOf(totalValueOfGoodsAndServices));

        //last update
        String lastUpdated = settings.getString("lastUploaded", null);
        ((TextView) findViewById(R.id.lastUpdate)).setText(lastUpdated.equals("null") ? "Never Updated!" : lastUpdated);

        //declare an NFC adapter
        adapter = NfcAdapter.getDefaultAdapter(this);

        //check if NFC is enabled
        boolean nfcEnabled = adapter.isEnabled();
        if(!nfcEnabled){
            Toast.makeText(getApplicationContext(), "Please activate NFC then press Back to return to the application!", Toast.LENGTH_LONG).show();
            startActivityForResult(new Intent(android.provider.Settings.ACTION_WIRELESS_SETTINGS), 0);
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
        if(adapter != null)
            adapter.disableForegroundDispatch(this);
    }
}
