package com.trade.barter.utils;

import android.content.Context;
import android.net.ConnectivityManager;
import android.widget.Toast;

import java.math.BigDecimal;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.text.DateFormat;
import java.text.DecimalFormat;
import java.text.DecimalFormatSymbols;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;

/**
 * Created by Adrian on 02/08/13.
 */
public class Utils {

    private static String salt = "a7y3ttk7go";

    public static String ByteArrayToHexString(byte [] inarray)
    {
        int i, j, in;
        String [] hex = {"0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F"};
        String out= "";

        for(j = 0 ; j < inarray.length ; ++j){
            in = (int) inarray[j] & 0xff;
            i = (in >> 4) & 0x0f;
            out += hex[i];
            i = in & 0x0f;
            out += hex[i];
        }
        return out;
    }

    public static String md5(String s) {
        try {
            //salt the password
            s = s + salt;

            // Create MD5 Hash
            MessageDigest digest = java.security.MessageDigest.getInstance("MD5");
            digest.update(s.getBytes());
            byte messageDigest[] = digest.digest();

            StringBuffer hexString = new StringBuffer();
            for (int i = 0; i < messageDigest.length; i++) {
                String h = Integer.toHexString(0xFF & messageDigest[i]);
                while (h.length() < 2)
                    h = "0" + h;
                hexString.append(h);
            }
            return hexString.toString();

        } catch (NoSuchAlgorithmException e) {
            e.printStackTrace();
        }
        return "";
    }

    public static Boolean checkConnectivity(Context context){
        try {
            ConnectivityManager connectivityManager = (ConnectivityManager) context.getSystemService(Context.CONNECTIVITY_SERVICE);
            if(connectivityManager.getNetworkInfo(ConnectivityManager.TYPE_WIFI).isConnected() || connectivityManager.getNetworkInfo(ConnectivityManager.TYPE_MOBILE).isConnected()){
                return true;
            }
            else {
                Toast.makeText(context, "In order to communicate with the server, you need a valid internet connection, please make sure you are connected to either a Wi-Fi hotspot or have data enabled from your Mobile Network Provider", Toast.LENGTH_LONG).show();
            }
        }
        catch (Exception e) {
            //temporarily print out the error connection message
            System.out.println("CheckConnectivity Exception: " + e.getMessage());
        }
        return false;
    }

    public static boolean convertIntToBoolean(int integerValue){
        //boolean convertedValue = integerValue > 0 ? true : false;
        return (integerValue != 0);
    }

    public static Double convertPrice(String priceString){
        if(priceString.equals("null")) priceString = "0";

        DecimalFormat twoDecimals = new DecimalFormat("0.00", new DecimalFormatSymbols());
        priceString = twoDecimals.format(Double.parseDouble(priceString));

        BigDecimal bd = new BigDecimal(Double.parseDouble(priceString));
        return (bd.setScale(2, BigDecimal.ROUND_HALF_UP)).doubleValue();
    }

    public static String convertPrice(Double priceString){
        DecimalFormat twoDecimals = new DecimalFormat("0.00", new DecimalFormatSymbols());
        return twoDecimals.format(priceString);
    }

    public static String convertServerTimestamp(String timestamp) throws ParseException {
        SimpleDateFormat oldFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        SimpleDateFormat newFormat = new SimpleDateFormat("dd-MM-yyyy HH:mm:ss");
        return newFormat.format(oldFormat.parse(timestamp));
    }

    public static String getCurrentDate(){
        //get the current time and date
        Calendar c = Calendar.getInstance();
        SimpleDateFormat sdf = new SimpleDateFormat("dd-MM-yyyy HH:mm:ss");
        return sdf.format(c.getTime());
    }

    public static String modifyDateLayout(String inputDateString, SimpleDateFormat originalFormat, SimpleDateFormat desiredFormat) throws java.text.ParseException{
        Date date = originalFormat.parse(inputDateString);
        String newDate = desiredFormat.format(date);

        return newDate;
    }
}


