package com.trade.barter.utils;

import android.app.Activity;
import android.content.Context;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;

public class AppSharedPreferences {

	private static final String LOGGED_IN = "logged in";
	private static final String NAME = "name";
	private static final String CARD_ID = "rfid card";
	private static final String GENDER = "gender";
	private static final String DOB = "date of birth";
	private static final String POSTCODE = "postcode";
	private static final String ETHICAL_PREFERENCES = "ethical preferences";
	private static final String EMAIL = "email";
	private static final String IS_TRADER = "is trader";
	private static final String IS_MANUFACTURER = "is manufacturer";
	private static final String IS_RETAILER = "is retailer";
	private static final String IS_SERVICE = "is service";
	private static final String IF_FIXED = "is fixed";
	private static final String IS_NOMADIC = "is nomadic";
	private static final String GOODS_SERVICES = "goods and services";
	private static final String STATEMENT = "statement";
	private static final String APP_SHARED_PREF = AppSharedPreferences.class.getSimpleName();
	private SharedPreferences sharedPreferences;
	private Editor editor;
	
	public AppSharedPreferences(Context context) {
		this.sharedPreferences = context.getSharedPreferences(APP_SHARED_PREF, Activity.MODE_PRIVATE);
		this.editor = sharedPreferences.edit();
	}

    public Boolean getLoggedIn() {
        return sharedPreferences.getBoolean(LOGGED_IN, false);
    }

    public String getName() {
        return sharedPreferences.getString(NAME, null);
    }

    public String getCardId() {
        return sharedPreferences.getString(CARD_ID, null);
    }

    public String getGender() {
        return sharedPreferences.getString(GENDER, null);
    }

    public String getDob() {
        return sharedPreferences.getString(DOB, null);
    }

    public String getPostcode() {
        return sharedPreferences.getString(POSTCODE, null);
    }

    public static String getEthicalPreferences() {
        return ETHICAL_PREFERENCES;
    }

    public static String getEmail() {
        return EMAIL;
    }

    public static String getIsTrader() {
        return IS_TRADER;
    }

    public static String getIsManufacturer() {
        return IS_MANUFACTURER;
    }

    public static String getIsRetailer() {
        return IS_RETAILER;
    }

    public static String getIsService() {
        return IS_SERVICE;
    }

    public static String getIfFixed() {
        return IF_FIXED;
    }

    public static String getIsNomadic() {
        return IS_NOMADIC;
    }

    public static String getGoodsServices() {
        return GOODS_SERVICES;
    }

    public static String getStatement() {
        return STATEMENT;
    }

    /*
	public void setImei(String id){
		editor.putString(IMEI, id);
		editor.commit();
	}
	
	public String getImei(){
		return sharedPreferences.getString(IMEI, null);
	}
	
	public Boolean isAppIdSet(String id){
		String savedID = sharedPreferences.getString(IMEI, "");
		if(savedID.equals(id)) return true;
		else return false;
	}
	
	public Boolean isTutorialDone(){
		return sharedPreferences.getBoolean(TUTORIAL_STATE, false);
	}
	
	public void setTutorialState(Boolean state){
		editor.putBoolean(TUTORIAL_STATE, state);
		editor.commit();
	}
	
	public void setPlayerId(int id){
		editor.putInt(PLAYER_ID, id);
		editor.commit();
	}
	
	public int getPlayerId(){
		return sharedPreferences.getInt(PLAYER_ID, 0);
	}
    */
}
