package com.trade.barter.utils;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.util.Log;

import java.math.BigDecimal;
import java.util.ArrayList;

public class DatabaseHandler extends SQLiteOpenHelper {

    //database version
    private static final int DATABASE_VERSION = 1;
    //database name
    private static final String DATABASE_NAME = "barter_database";

    //declaring the variable names for the table of transaction
    private static final String TBL_TRANSACTIONS = "tbl_transactions";
    private static final String TRADER_ID = "trader_id";
    private static final String TRANS_ID = "trans_ID";
    private static final String CONSUMER_ID = "consumer_rfid";
    private static final String TRANS_LAT = "trans_lat";
    private static final String TRANS_LON = "trans_lon";
    private static final String TRANS_TYPE = "trans_type";
    private static final String TRANS_ORIGIN = "trans_origin";
    private static final String TRANS_PRICE = "trans_price";
    private static final String TRANS_POINTS = "trans_rating";
    private static final String TRANS_TIMESTAMP = "trans_timestamp";
    private static final String TRANS_SYNC_STATUS = "trans_synced";

    //declaring the variable names for the table of consumer totals
    private static final String TBL_CONSUMER_TOTALS = "tbl_consumer_totals";
    private static final String TOTAL_ID = "total_id";
    private static final String CONSUMER_TOTAL = "consumer_total";
    private static final String CONSUMER_TOTAL_POINTS = "consumer_total_points";
    private static final String CONSUMER_TOTAL_TRANSACTIONS = "consumer_total_transactions";
    private static final String LAST_UPDATE = "last_update";

    //declaring the variable names for the table of redeems
    private static final String TBL_REDEEMS = "tbl_redeems";
    private static final String REDEEM_ID = "redeem_id";
    private static final String REDEEM_TYPE = "redeem_type";
    private static final String POINTS_DEDUCTED =  "no_of_points_deducted";
    private static final String REDEEM_TIMESTAMP =  "redeem_timestamp";
    private static final String REDEEM_SYNC_STATUS =  "redeem_synced";

    public DatabaseHandler(Context context){
        super(context, DATABASE_NAME, null, DATABASE_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        //declare and create the transaction table
        String CREATE_TRANSACTION_TABLE = "CREATE TABLE IF NOT EXISTS " + TBL_TRANSACTIONS + "("
                + TRANS_ID + " INTEGER PRIMARY KEY, " + TRADER_ID + " TEXT, " + CONSUMER_ID + " TEXT, "
                + TRANS_LAT + " REAL, " + TRANS_LON + " REAL, " + TRANS_TYPE + " TEXT, "
                + TRANS_ORIGIN + " TEXT, "+ TRANS_PRICE + " TEXT, " + TRANS_POINTS + " INTEGER, "
                + TRANS_TIMESTAMP + " TEXT, " + TRANS_SYNC_STATUS + " INTEGER"
                + ")";
        db.execSQL(CREATE_TRANSACTION_TABLE);

        String CREATE_CONSUMER_TOTAL_TABLE = "CREATE TABLE IF NOT EXISTS " + TBL_CONSUMER_TOTALS + "("
                + TOTAL_ID + " INTEGER PRIMARY KEY, " + CONSUMER_ID + " TEXT, "
                + CONSUMER_TOTAL + " REAL, " + CONSUMER_TOTAL_POINTS + " INTEGER, "
                + CONSUMER_TOTAL_TRANSACTIONS + " INTEGER, " + LAST_UPDATE + " TEXT"
                + ")";
        db.execSQL(CREATE_CONSUMER_TOTAL_TABLE);

        String CREATE_REDEEM_TABLE = "CREATE TABLE IF NOT EXISTS " + TBL_REDEEMS + "("
                + REDEEM_ID + " INTEGER PRIMARY KEY, " + TRADER_ID + " TEXT, "
                + CONSUMER_ID + " TEXT, " + REDEEM_TYPE + " TEXT, "
                + POINTS_DEDUCTED + " INTEGER, " + REDEEM_TIMESTAMP + " TEXT, "
                + REDEEM_SYNC_STATUS + " INTEGER"
                + ")";
        db.execSQL(CREATE_REDEEM_TABLE);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {}

    public void addTransaction(Transaction trans){

        SQLiteDatabase db = this.getWritableDatabase();

        //allow the database to create the values to be insert
        ContentValues values = new ContentValues();
        values.put(TRADER_ID, trans.getTraderID());
        values.put(CONSUMER_ID, trans.getConsumerID());
        values.put(TRANS_LAT, trans.getLatitude());
        values.put(TRANS_LON, trans.getLongitude());
        values.put(TRANS_TYPE, trans.getType());
        values.put(TRANS_ORIGIN, trans.getOrigin());
        values.put(TRANS_PRICE, trans.getPrice());
        values.put(TRANS_POINTS, trans.getPoints());
        values.put(TRANS_TIMESTAMP, trans.getTimestamp());
        values.put(TRANS_SYNC_STATUS, trans.isSyncStatus());

        //insert the data into the database
        db.insert(TBL_TRANSACTIONS, null, values);
        //close the database
        db.close();
    }

    public void updateTransactionStatus(int transactionID){

        SQLiteDatabase db = this.getWritableDatabase();

        //allow the database to create the values to be insert
        ContentValues values = new ContentValues();
        values.put(TRANS_SYNC_STATUS, 1); //equivalent of true
        //insert the data into the database
        db.update(TBL_TRANSACTIONS, values, TRANS_ID + "='" + transactionID + "'", null);

        //close the database
        db.close();

        //update the SO to reflect correct number

    }

    public ArrayList<Transaction> getTransactions(){

        ArrayList<Transaction> transactions = new ArrayList<Transaction>();

        String sql = "SELECT * FROM " + TBL_TRANSACTIONS + " WHERE " + TRANS_SYNC_STATUS + " = 0" + " ORDER BY " + TRANS_ID;
        SQLiteDatabase db = this.getWritableDatabase();

        Cursor cursor = db.rawQuery(sql, null);

        //save every event to the events list array
        if(cursor.moveToFirst()){
            do{
                //create a new temporary transaction
                Transaction tempTrans = new Transaction(cursor.getInt(0), cursor.getString(1), cursor.getString(2), cursor.getDouble(3), cursor.getDouble(4), cursor.getString(5), cursor.getString(6), cursor.getDouble(7), cursor.getInt(8), cursor.getString(9), Utils.convertIntToBoolean(cursor.getInt(10)));
                //add to the transaction array list
                transactions.add(tempTrans);
            }
            while (cursor.moveToNext());
        }
        db.close();
        return transactions;
    }

    public void addConsumerData(ConsumerData consumerData){

        //verify if the consumer is already in the database
        String sql = "SELECT count(*) FROM " + TBL_CONSUMER_TOTALS + " WHERE " + CONSUMER_ID + " = '" + consumerData.getConsumerId() + "'";
        SQLiteDatabase db = this.getWritableDatabase();

        Cursor cursor = db.rawQuery(sql, null);
        cursor.moveToFirst();

        ConsumerData oldData = new ConsumerData(consumerData.getConsumerId(), 0.0, 0, 0, null);

        if(cursor.getInt(0) == 0){
            ContentValues values = new ContentValues();
            values.put(CONSUMER_ID, consumerData.getConsumerId());
            values.put(CONSUMER_TOTAL, consumerData.getTotalSpent());
            values.put(CONSUMER_TOTAL_POINTS, consumerData.getTotalPoints());
            values.put(CONSUMER_TOTAL_TRANSACTIONS, consumerData.getTotalTransactions());
            values.put(LAST_UPDATE, consumerData.getLastUpdate());
            //insert the data into the database
            db.insert(TBL_CONSUMER_TOTALS, null, values);
        }
        else {

            sql = "SELECT * FROM " + TBL_CONSUMER_TOTALS + " WHERE " + CONSUMER_ID + " = '" + consumerData.getConsumerId() + "'";
            cursor = db.rawQuery(sql, null);

            //need to first get the current values from the database and merge with the current ones
            if(cursor.moveToFirst()){
                do{
                    oldData = new ConsumerData(cursor.getString(1), cursor.getDouble(2), cursor.getInt(3), cursor.getInt(4), cursor.getString(5));
                }
                while (cursor.moveToNext());
            }

            //update the fields
            ContentValues values = new ContentValues();
            values.put(CONSUMER_TOTAL, (consumerData.getTotalSpent() + oldData.getTotalSpent()));
            values.put(CONSUMER_TOTAL_POINTS, (consumerData.getTotalPoints() + oldData.getTotalPoints()));
            values.put(CONSUMER_TOTAL_TRANSACTIONS, (consumerData.getTotalTransactions() + oldData.getTotalTransactions()));
            values.put(LAST_UPDATE, consumerData.getLastUpdate());

            //insert the data into the database
            db.update(TBL_CONSUMER_TOTALS, values, CONSUMER_ID + "='" + consumerData.getConsumerId() + "'", null);
        }
        db.close();
    }

    public ConsumerData getConsumerData(String nfcCardId){
        //verify if the consumer is already in the database
        String sql = "SELECT * FROM " + TBL_CONSUMER_TOTALS + " WHERE " + CONSUMER_ID + " = '" + nfcCardId + "'";
        SQLiteDatabase db = this.getWritableDatabase();

        Cursor cursor = db.rawQuery(sql, null);

        ConsumerData consumerData = new ConsumerData(nfcCardId, 0.0, 0, 0, null);
        if(cursor.moveToFirst()){
            do{
                consumerData = new ConsumerData(cursor.getInt(0), cursor.getString(1), cursor.getDouble(2), cursor.getInt(3), cursor.getInt(4), cursor.getString(5));
            }
            while (cursor.moveToNext());
        }

        db.close();
        //Log.d("TEST", consumerData.getTotalPoints() + " - " + consumerData.getTotalId());
        return consumerData;
    }

    public void overrideTransaction(Transaction trans){

        SQLiteDatabase db = this.getWritableDatabase();

        //allow the database to create the values to be insert
        ContentValues values = new ContentValues();
        values.put(TRANS_PRICE, trans.getPrice());
        values.put(TRANS_POINTS, trans.getPoints());
        values.put(TRANS_TYPE, trans.getType());

        //insert the data into the database
        db.update(TBL_TRANSACTIONS, values, TRANS_ID + "='" + trans.getTransactionID() + "'", null);
        //close the database
        db.close();
    }

    public void overrideConsumerStats(String consumerId, int total, int points, int occurrences, String lastUpdate){
        SQLiteDatabase db = this.getWritableDatabase();

        ContentValues values = new ContentValues();
        values.put(CONSUMER_TOTAL, total);
        values.put(CONSUMER_TOTAL_POINTS, points);
        values.put(CONSUMER_TOTAL_TRANSACTIONS, occurrences);
        values.put(LAST_UPDATE, lastUpdate);

        //insert the data into the database

        if(db.update(TBL_CONSUMER_TOTALS, values, CONSUMER_ID + "='" + consumerId + "'", null) != 1){
            values.put(CONSUMER_ID, consumerId);
            db.insert(TBL_CONSUMER_TOTALS, null, values);
        }
        Log.e("BARTER", "Consumer id: " + consumerId + " updated!");
        db.close();
    }

    public void updateConsumerStats(String consumerId, int total, int points, int occurrences, String lastUpdate){
        SQLiteDatabase db = this.getWritableDatabase();

        ContentValues values = new ContentValues();
        values.put(CONSUMER_ID, consumerId);
        values.put(CONSUMER_TOTAL, total);
        values.put(CONSUMER_TOTAL_POINTS, points);
        values.put(CONSUMER_TOTAL_TRANSACTIONS, occurrences);
        values.put(LAST_UPDATE, lastUpdate);

        //insert the data into the database
        db.insert(TBL_CONSUMER_TOTALS, null, values);
        db.close();
    }

    public void updateConsumerData(String consumerId, int pointsDeducted){
        //retrieve the old data
        String sql = "SELECT * FROM " + TBL_CONSUMER_TOTALS + " WHERE " + CONSUMER_ID + " = '" + consumerId + "'";
        SQLiteDatabase db = this.getWritableDatabase();

        Cursor cursor = db.rawQuery(sql, null);

        ConsumerData consumerData = null;
        if(cursor.moveToFirst()){
            do{
                consumerData = new ConsumerData(cursor.getString(1), cursor.getDouble(2), cursor.getInt(3), cursor.getInt(4), cursor.getString(5));
            }
            while (cursor.moveToNext());
        }

        ContentValues values = new ContentValues();
        values.put(CONSUMER_TOTAL_POINTS, (consumerData.getTotalPoints() - pointsDeducted));
        values.put(LAST_UPDATE, consumerData.getLastUpdate());

        //insert the data into the database
        db.update(TBL_CONSUMER_TOTALS, values, CONSUMER_ID + "='" + consumerId + "'", null);
        db.close();
    }

    public void updateConsumerData(String consumerId, double oldTransactionPrice, int oldTransactionPoints, double newTransactionPrice, int newTransactionPoints){

        //retrieve the old data
        String sql = "SELECT * FROM " + TBL_CONSUMER_TOTALS + " WHERE " + CONSUMER_ID + " = '" + consumerId + "'";
        SQLiteDatabase db = this.getWritableDatabase();

        Cursor cursor = db.rawQuery(sql, null);

        ConsumerData consumerData = null;
        if(cursor.moveToFirst()){
            do{
                consumerData = new ConsumerData(cursor.getString(1), cursor.getDouble(2), cursor.getInt(3), cursor.getInt(4), cursor.getString(5));
            }
            while (cursor.moveToNext());
        }

        ContentValues values = new ContentValues();
        values.put(CONSUMER_TOTAL, (consumerData.getTotalSpent() - oldTransactionPrice + newTransactionPrice));
        values.put(CONSUMER_TOTAL_POINTS, (consumerData.getTotalPoints() - oldTransactionPoints + newTransactionPoints));
        values.put(LAST_UPDATE, consumerData.getLastUpdate());

        //insert the data into the database
        db.update(TBL_CONSUMER_TOTALS, values, CONSUMER_ID + "='" + consumerId + "'", null);
        db.close();
    }

    public int TotalNumberOfTransactions(){

        int sum = 0;

        String sql = "SELECT count(*) FROM " + TBL_TRANSACTIONS;
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.moveToFirst()){
            sum = cursor.getInt(0);
        }
        db.close();

        return sum;
    }

    public int getTotalNumberOfMobileTransactions(){

        int sum = 0;

        String sql = "SELECT count(*) FROM " + TBL_TRANSACTIONS + " WHERE " + TRANS_SYNC_STATUS + " = 1";
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.moveToFirst()){
            sum = cursor.getInt(0);
        }
        db.close();

        return sum;
    }

    public int getTotalNumberOfUnsyncedTransactions(){

        int sum = 0;

        String sql = "SELECT count(*) FROM " + TBL_TRANSACTIONS + " WHERE " + TRANS_SYNC_STATUS + " = 0";
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.moveToFirst()){
            sum = cursor.getInt(0);
        }
        db.close();

        return sum;
    }

    public double TotalSpentByCustomer(String nfcID){

        double sum = 0.00;
        String sql = "SELECT sum(" + TRANS_PRICE +") AS Sum FROM " + TBL_TRANSACTIONS + " WHERE " + CONSUMER_ID + " = '"+nfcID+"'";
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.moveToFirst()){
            sum = cursor.getDouble(0);
        }

        db.close();

        sum = round(sum, 2, BigDecimal.ROUND_HALF_UP);

        return sum;
    }



    public int getTotalNumberOfSyncedTransactions(){

        int sum = 0;

        String sql = "SELECT COUNT(*) FROM " + TBL_TRANSACTIONS + " WHERE " + TRANS_SYNC_STATUS + " = '1'";
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.moveToFirst()){
            sum = cursor.getInt(0);
        }
        db.close();

        return sum;
    }

    public double getTotalValueOfTransactions(){

        double sum = 0.00;

        String sql = "SELECT SUM(" + TRANS_PRICE + ") FROM " + TBL_TRANSACTIONS;
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.moveToFirst()){
            sum = cursor.getDouble(0);
        }
        db.close();

        return round(sum, 2, BigDecimal.ROUND_HALF_UP);
    }

    public double getTotalValueOfGoods(){

        double sum = 0.00;

        String sql = "SELECT SUM(" + TRANS_PRICE + ") FROM " + TBL_TRANSACTIONS + " WHERE " + TRANS_TYPE + " = 'goods'";
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.moveToFirst()){
            sum = cursor.getDouble(0);
        }
        db.close();

        return round(sum, 2, BigDecimal.ROUND_HALF_UP);
    }

    public double getTotalValueOfServices(){

        double sum = 0.00;

        String sql = "SELECT SUM(" + TRANS_PRICE + ") FROM " + TBL_TRANSACTIONS + " WHERE " + TRANS_TYPE + " = 'services'";
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.moveToFirst()){
            sum = cursor.getDouble(0);
        }
        db.close();

        return round(sum, 2, BigDecimal.ROUND_HALF_UP);
    }

    public double getTotalValueOfGoodsAndServices(){

        double sum = 0.00;

        String sql = "SELECT SUM(" + TRANS_PRICE + ") FROM " + TBL_TRANSACTIONS + " WHERE " + TRANS_TYPE + " = 'both'";
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.moveToFirst()){
            sum = cursor.getDouble(0);
        }
        db.close();

        return round(sum, 0, BigDecimal.ROUND_HALF_UP);
    }

    public void addRedeem(Redeem redeem){

        SQLiteDatabase db = this.getWritableDatabase();

        //allow the database to create the values to be insert
        ContentValues values = new ContentValues();
        values.put(TRADER_ID, redeem.getTraderID());
        values.put(CONSUMER_ID, redeem.getConsumerRFID());
        values.put(REDEEM_TYPE, redeem.getRedeemType());
        values.put(POINTS_DEDUCTED, redeem.getPointsDeducted());
        values.put(REDEEM_TIMESTAMP, redeem.getTimestamp());
        values.put(REDEEM_SYNC_STATUS, redeem.isSynced());

        //insert the data into the database
        db.insert(TBL_REDEEMS, null, values);
        //close the database
        db.close();
    }

    public void updateRedeemStatus(int redeemID){

        SQLiteDatabase db = this.getWritableDatabase();

        //allow the database to create the values to be insert
        ContentValues values = new ContentValues();
        values.put(REDEEM_SYNC_STATUS, 1); //equivalent of true
        //insert the data into the database
        db.update(TBL_REDEEMS, values, REDEEM_ID + "='" + redeemID + "'", null);

        //close the database
        db.close();
    }

    public ArrayList<Redeem> getRedeems(){

        ArrayList<Redeem> redeems = new ArrayList<Redeem>();

        String sql = "SELECT * FROM " + TBL_REDEEMS + " WHERE " + REDEEM_SYNC_STATUS + " = 0" + " ORDER BY " + REDEEM_ID;
        SQLiteDatabase db = this.getWritableDatabase();

        Cursor cursor = db.rawQuery(sql, null);

        //save every event to the events list array
        if(cursor.moveToFirst()){
            do{
                //create a new temporary redeem
                Redeem tempRedeem = new Redeem(cursor.getInt(0), cursor.getString(1), cursor.getString(2), cursor.getString(3), cursor.getInt(4), cursor.getString(5), Utils.convertIntToBoolean(cursor.getInt(6)));
                //add to the transaction array list
                redeems.add(tempRedeem);
            }
            while (cursor.moveToNext());
        }
        db.close();
        return redeems;
    }

    public int getTotalNumberOfUnSyncedRedeems(){

        int sum = 0;

        String sql = "SELECT COUNT(*) FROM " + TBL_REDEEMS + " WHERE " + REDEEM_SYNC_STATUS + " = '0'";
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(sql, null);

        if(cursor.moveToFirst()){
            sum = cursor.getInt(0);
        }
        db.close();

        return sum;
    }

//    public String getLastUpdate(){
//
//        String lastUpdate = null;
//
//        String sql = "SELECT " + LAST_UPDATE + " FROM " + TBL_CONSUMER_TOTALS;
//        SQLiteDatabase db = this.getWritableDatabase();
//        Cursor cursor = db.rawQuery(sql, null);
//
//        if(cursor.moveToFirst()){
//            lastUpdate = cursor.getString(0);
//        }
//        db.close();
//        return lastUpdate;
//    }

    public void deleteAllRecords(){
        SQLiteDatabase db = this.getWritableDatabase();
        db.delete(TBL_TRANSACTIONS,null,null);
        db.delete(TBL_CONSUMER_TOTALS,null,null);
        db.close();
    }

    public static double round(double unrounded, int precision, int roundingMode){
        BigDecimal bd = new BigDecimal(unrounded);
        BigDecimal rounded = bd.setScale(precision, roundingMode);
        return rounded.doubleValue();
    }
}