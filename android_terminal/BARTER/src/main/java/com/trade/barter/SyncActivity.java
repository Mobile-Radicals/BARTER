package com.trade.barter;

import android.app.ListActivity;
import android.app.ProgressDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.drawable.ColorDrawable;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;

import com.trade.barter.api.ApiDoohickey;
import com.trade.barter.api.ApiTransaction;
import com.trade.barter.utils.DatabaseHandler;
import com.trade.barter.utils.Redeem;
import com.trade.barter.utils.Transaction;
import com.trade.barter.utils.TransactionAdapter;
import com.trade.barter.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

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

    private boolean noTransactions = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.sync_main);

        this.getActionBar().setDisplayHomeAsUpEnabled(true);
        this.getActionBar().setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.pale_red)));

        //get the current shared preferences
        settings = this.getSharedPreferences(getString(R.string.preferences), 0);
    }

    @Override
    protected void onNewIntent(Intent intent){
        Log.d(getString(R.string.app_name), "NFC intent was discovered");
    }

    @Override
    protected void onPause(){
        super.onPause();
        NFCState.onPause(this);    
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

        NFCState.check(this);
        NFCState.givePriority(this);
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
                        ApiDoohickey.getInstance().uploadRedeems(dialog,allRedeems,noTransactions,this);
                    }

                    if(transactionsPositions.size() != 0){
                        dialog.show();
                        ApiDoohickey.getInstance().uploadTransactions(dialog,transactionsPositions,transactions,this);
                    }
                    else{
                        if(transactions.size() != 0){
                            dialog.show();
                            ApiDoohickey.getInstance().uploadTransactions(dialog,null,transactions,this);
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
}
