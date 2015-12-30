package com.trade.barter.api;

import java.text.ParseException;
import java.util.ArrayList;
import java.util.Iterator;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.Dialog;
import android.app.ProgressDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.util.Log;
import android.widget.TextView;
import android.widget.Toast;

import com.trade.barter.MainActivity;
import com.trade.barter.R;
import com.trade.barter.SigninActivity;
import com.trade.barter.SyncActivity;
import com.trade.barter.utils.DatabaseHandler;
import com.trade.barter.utils.Redeem;
import com.trade.barter.utils.Transaction;
import com.trade.barter.utils.TransactionAdapter;
import com.trade.barter.utils.Utils;

public class ApiDoohickey {
	
	private SharedPreferences sharedPreferences;
	private DatabaseHandler db;
	private static ApiDoohickey instance;
	
	public static ApiDoohickey getInstance()
	{
		if(instance==null)
		{
			instance=new ApiDoohickey(MainActivity.getContext().getSharedPreferences(MainActivity.getContext().getString(R.string.preferences), 0));
		}
		return instance;
	}
	
	private ApiDoohickey(SharedPreferences sharedPreferences)
	{
		this.sharedPreferences=sharedPreferences;
		db = new DatabaseHandler(MainActivity.getContext());
	}
	
	public void getSync(Dialog dialog){

        JSONObject traderJson = new JSONObject();
        try {
            traderJson.put("trader_id", sharedPreferences.getString("cardId", null));
        } catch (Exception e) {
            Log.e(MainActivity.getContext().getString(R.string.app_name), "JSON exception from consumer's data");
        }

        String dataToSend = traderJson.toString();

        String[] params = new String[2];
        params[0] = MainActivity.getContext().getString(R.string.get_sync_data);
        params[1] = dataToSend;

        new ApiTransaction(null){
        	@Override
            protected void result(JSONObject data) throws Exception{
                Log.i(MainActivity.getContext().getString(R.string.app_name), "All transactions have been successfully uploaded to the database");

                JSONArray consumersToBeUpdated = data.getJSONArray("customerData");
                JSONObject traderStats = data.getJSONObject("traderTotals");

                for(int i = 0; i < consumersToBeUpdated.length(); i++){
                    JSONObject consumer = consumersToBeUpdated.getJSONObject(i);
                    db.overrideConsumerStats(consumer.getString("customer_id"), consumer.getInt("customer_spend"), consumer.getInt("customer_points"), consumer.getInt("customer_occurrences"), consumer.getString("timestamp"));
                }

                updateTraderStats(traderStats);

                dialog.dismiss();
                Toast.makeText(MainActivity.getContext().getApplicationContext(), "All data has been successfully synced.", Toast.LENGTH_LONG).show();       
            }
        }.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }
	
	public void uploadAllTransactions(Dialog dialog, final ArrayList<Transaction> transactions){
        JSONArray jsonArrayTransaction = new JSONArray();

        for(Transaction transaction : transactions){
            JSONObject transactionJson = new JSONObject();
            try {
                transactionJson.put("trader_id", sharedPreferences.getString("cardId", null));
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
                Log.e(MainActivity.getContext().getString(R.string.app_name), "JSON exception from consumer's data");
            }

            jsonArrayTransaction.put(transactionJson);
        }

        String[] params = new String[2];
        params[0] = MainActivity.getContext().getString(R.string.auto_manual_sync_url);
        params[1] = jsonArrayTransaction.toString();

        new ApiTransaction(dialog){

			@Override
			protected void result(JSONObject data) throws Exception {
				Log.i(MainActivity.getContext().getString(R.string.app_name), "All transactions have been successfully uploaded to the database");

                for(Iterator<Transaction> it = transactions.iterator(); it.hasNext();){
                    Transaction transaction = it.next();
                    //remove the current transaction from the transaction ArrayList
                    it.remove();
                    //update the current transaction into the database
                    db.updateTransactionStatus(transaction.getTransactionID());
                }

                JSONArray consumersToBeUpdated = data.getJSONArray("customerData");
                JSONObject traderStats = data.getJSONObject("traderTotals");

                //parse the sync data
                for(int i = 0; i < consumersToBeUpdated.length(); i++){
                    JSONObject consumer = consumersToBeUpdated.getJSONObject(i);
                    db.overrideConsumerStats(consumer.getString("customer_id"), consumer.getInt("customer_spend"), consumer.getInt("customer_points"), consumer.getInt("customer_occurrences"), consumer.getString("timestamp"));
                }

                updateTraderStats(traderStats);

                dialog.dismiss();
                Toast.makeText(MainActivity.getContext(), "All transactions have been successfully uploaded.", Toast.LENGTH_LONG).show();
			}
        	
        }.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }
	
	//TODO: THIS SEEMS TO BE UNUSED. There is some commented out code in Redeem Activity that references the old method. If we need it, we need to put ApiDoohickey().getInstance(). in front.
	public void uploadRedeem(Dialog dialog,final Redeem redeem){
        JSONArray jsonArrayRedeem = new JSONArray();
        JSONObject redeemJson = new JSONObject();
        try {
            redeemJson.put("trader_id", sharedPreferences.getInt("id", 0));
            redeemJson.put("redeem_id", redeem.getRedeemID());
            redeemJson.put("consumer_id", redeem.getConsumerRFID());
            redeemJson.put("points_deducted", redeem.getPointsDeducted());
            redeemJson.put("redeem_timestamp", redeem.getTimestamp());
        } catch (Exception e) {
            Log.e(MainActivity.getContext().getString(R.string.app_name), "JSON exception from redeem data.");
        }

        jsonArrayRedeem.put(redeemJson);

        String[] params = new String[3];
        params[0] = MainActivity.getContext().getString(R.string.redeem_url);
        params[1] = jsonArrayRedeem.toString();

        new ApiTransaction(dialog){

			@Override
			protected void result(JSONObject data) throws Exception {
				Log.i(MainActivity.getContext().getString(R.string.app_name), "Redeem has been successfully added to the database!");

                db.updateRedeemStatus(redeem.getRedeemID());

                dialog.dismiss();
                Toast.makeText(MainActivity.getContext(), "Your option was saved!.", Toast.LENGTH_LONG).show();
				
			}
        	
        }.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }
	
	
	public void uploadRedeems(Dialog dialog, final ArrayList<Redeem> allRedeems, final boolean noTransactions, final SyncActivity syncActivity){
        JSONArray jsonArrayRedeems = new JSONArray();

        for(Redeem redeem: allRedeems){

            JSONObject redeemJson = new JSONObject();
            try {
                redeemJson.put("trader_id", sharedPreferences.getString("cardId",null));
                redeemJson.put("redeem_id", redeem.getRedeemID());
                redeemJson.put("consumer_id", redeem.getConsumerRFID());
                redeemJson.put("redeem_type", redeem.getRedeemType());
                redeemJson.put("points_deducted", redeem.getPointsDeducted());
                redeemJson.put("redeem_timestamp", redeem.getTimestamp());

            } catch (Exception e) {
                Log.e(MainActivity.getContext().getString(R.string.app_name), "JSON exception from redeem data.");
            }

            Log.i(MainActivity.getContext().getString(R.string.app_name), "Type: " + redeem.getRedeemType());

            jsonArrayRedeems.put(redeemJson);
        }

        String[] params = new String[2];
        params[0] = MainActivity.getContext().getString(R.string.redeem_url);
        params[1] = jsonArrayRedeems.toString();

        new ApiTransaction(dialog){

			@Override
			protected void result(JSONObject data) throws Exception {
				Log.i(MainActivity.getContext().getString(R.string.app_name), "All redeems have been successfully uploaded to the database.");

                Redeem redeem;

                for(Iterator<Redeem> it = allRedeems.iterator(); it.hasNext();){

                    redeem = it.next();
                    redeem.setSynced(true);

                    it.remove();
                    //update the current transaction into the database
                    db.updateRedeemStatus(redeem.getRedeemID());
                }

                ((TextView) MainActivity.getInstance().findViewById(R.id.textView2)).setText("0");
                dialog.dismiss();
                Toast.makeText(MainActivity.getContext(), "All redeems have been successfully uploaded to the database.", Toast.LENGTH_SHORT).show();
            	if(noTransactions) {
                    syncActivity.startActivity(new Intent(MainActivity.getContext(), MainActivity.class).addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP));
                    syncActivity.finish();
                }
			}
        	
        }.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }
	
	
    
	
	public void loginTrader(final SigninActivity signinActivity, final String email, final String password){

        ProgressDialog dialog = new ProgressDialog(signinActivity);
        dialog.setMessage("Logging in...");
        dialog.setIndeterminate(true);
        dialog.setCancelable(false);
        dialog.show();

        JSONObject traderLogin = new JSONObject();
        try {
            traderLogin.put("email", email);
            traderLogin.put("password", password);
        } catch (Exception e) {
            Log.i("Exception while uploading the trader's credentials", e.getMessage());
        }

        String[] params = new String[2];
        params[0] = MainActivity.getContext().getString(R.string.login_url);
        params[1] = traderLogin.toString();

        new ApiTransaction(dialog){

			@Override
			protected void result(JSONObject data) throws Exception {
				JSONObject traderDetails = data.getJSONObject("details");
                JSONObject traderStats = data.getJSONObject("traderTotals");
                JSONArray consumersToBeUpdated = data.getJSONArray("customerData");

                try {
                    SharedPreferences.Editor editor = sharedPreferences.edit();

                    editor.putInt("id", traderDetails.getInt("id"));
                    editor.putString("name", traderDetails.getString("name"));
                    editor.putString("cardId", traderDetails.getString("cardId"));
                    editor.putString("gender", traderDetails.getString("gender"));
                    editor.putString("dob", traderDetails.getString("dob"));
                    editor.putString("email", email);
                    editor.putString("password", Utils.md5(password)); //TODO: -------------------------- Why does this need to be saved to shared preferences? Hashed or otherwise? It is already hashed too...
                    editor.putString("postcode", traderDetails.getString("postcode"));
                    editor.putString("preferences", traderDetails.getString("preferences"));
                    editor.putBoolean("isManufacturer", Utils.convertIntToBoolean(traderDetails.getInt("isManufacturer")));
                    editor.putBoolean("isRetailer", Utils.convertIntToBoolean(traderDetails.getInt("isRetailer")));
                    editor.putBoolean("isService", Utils.convertIntToBoolean(traderDetails.getInt("isService")));
                    editor.putBoolean("fixed", Utils.convertIntToBoolean(traderDetails.getInt("fixed")));
                    editor.putBoolean("nomadic", Utils.convertIntToBoolean(traderDetails.getInt("nomadic")));
                    editor.putString("goodsServices", traderDetails.getString("goodsServices"));
                    editor.putString("statement", traderDetails.getString("statement"));

                    if(Utils.convertIntToBoolean(traderDetails.getInt("isManufacturer"))){
                        editor.putString("businessProduce", "goods");
                    }
                    if(Utils.convertIntToBoolean(traderDetails.getInt("isRetailer"))){
                        editor.putString("businessProduce", "both");
                    }
                    if(Utils.convertIntToBoolean(traderDetails.getInt("isService"))){
                        editor.putString("businessProduce", "services");
                    }

                    editor.putBoolean("loggedIn", true);

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

                    editor.putBoolean("needsUpdating", true);

                    editor.commit();

                    Log.e("LOGIN", sharedPreferences.getString("lastUploaded", "Last updated"));

                    for(int i = 0; i < consumersToBeUpdated.length(); i++){
                        JSONObject consumer = consumersToBeUpdated.getJSONObject(i);
                        db.updateConsumerStats(consumer.getString("customer_id"), consumer.getInt("customer_spend"), consumer.getInt("customer_points"), consumer.getInt("customer_occurrences"), consumer.getString("timestamp"));
                    }

                    dialog.dismiss();

                    signinActivity.startActivity(new Intent(MainActivity.getContext(), MainActivity.class));
                    signinActivity.finish();

                } catch (Exception e) {
                    dialog.dismiss();
                    Log.e(MainActivity.getContext().getString(R.string.app_name), e.getMessage());
                }
			}
        	
        }.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }
	
	public void uploadTransactions(Dialog dialog, ArrayList<Integer> transactionsPositions, final ArrayList<Transaction> transactions, final SyncActivity syncActivity){

		final TransactionAdapter transactionAdapter = new TransactionAdapter(syncActivity, transactions);
        JSONArray jsonArrayTransaction = new JSONArray();

        final String[] params = new String[3];
        params[0] = MainActivity.getContext().getString(R.string.upload_url);
        if(transactionsPositions==null)
        {
        	for(Transaction transaction : transactions){
                jsonArrayTransaction.put(JSONTransactionBuilder(transaction));
            }
        	params[1] = jsonArrayTransaction.toString();
        	params[2] = "all";
        }
        else
        {	
        	for(Integer transPosition : transactionsPositions){
                jsonArrayTransaction.put(JSONTransactionBuilder(transactions.get(transPosition)));
            }
        	params[1] = jsonArrayTransaction.toString();
        	params[2] = "selected";
        }

        new ApiTransaction(dialog){

			@Override
			protected void result(JSONObject data) throws Exception {
				Log.i(MainActivity.getContext().getString(R.string.app_name), "All transactions have been successfully uploaded to the database");
                syncActivity.startActivity(new Intent(MainActivity.getContext().getApplicationContext(), MainActivity.class).addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP));
                syncActivity.finish();

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
                Toast.makeText(MainActivity.getContext(), "All selected transactions have been successfully uploaded.", Toast.LENGTH_LONG).show();
			}
        	
        }.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }
    
	private JSONObject JSONTransactionBuilder(Transaction transaction)
	{
		JSONObject transactionJson = new JSONObject();
        try {
            transactionJson.put("trader_id", sharedPreferences.getString("cardId", null));
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
            Log.e(MainActivity.getContext().getString(R.string.app_name), "JSON exception from transaction data.");
        }
        return transactionJson;
	}
	
	// Other stuff
	
	
	private void updateTraderStats(JSONObject traderStats) throws JSONException, ParseException {

		SharedPreferences.Editor editor=sharedPreferences.edit();
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
        MainActivity.getInstance().invalidateOptionsMenu();
    }
	
	private void updateTraderStats(Transaction transaction){
        SharedPreferences.Editor editor = sharedPreferences.edit();

        //update all trader's stats
        editor.putInt("totalMobileTransactions", (sharedPreferences.getInt("totalMobileTransactions", 0)+ 1));

        if(transaction.getType().equals("goods")){
            editor.putString("totalPriceGoods", String.valueOf(transaction.getPrice() + Double.parseDouble(sharedPreferences.getString("totalPriceGoods", ""))));
        }
        else if(transaction.getType().equals("services")){
            editor.putString("totalPriceServices", String.valueOf(transaction.getPrice() + Double.parseDouble(sharedPreferences.getString("totalPriceServices", ""))));
        }
        else if(transaction.getType().equals("both")){
            editor.putString("totalPriceBoth", String.valueOf(transaction.getPrice() + Double.parseDouble(sharedPreferences.getString("totalPriceBoth", ""))));
        }
        editor.commit();
    }
}
