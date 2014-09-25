package com.trade.barter.utils;

import android.app.Activity;
import android.content.Context;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.RatingBar;
import android.widget.TextView;

import com.trade.barter.R;

import java.text.SimpleDateFormat;
import java.util.ArrayList;

public class TransactionAdapter extends BaseAdapter {
	
	private ArrayList<Transaction> transactions;
	private ArrayList<Boolean> checkedBoxes = new ArrayList<Boolean>();;
    private static LayoutInflater inflater = null;
	
	public TransactionAdapter(Activity activity, ArrayList<Transaction> transactions){
		inflater = (LayoutInflater) activity.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
		this.transactions = transactions;
		for(int i = 0; i < this.getCount(); i++){
			checkedBoxes.add(i, false);
		}
	}

	@Override
	public int getCount() {
		return transactions.size();
	}

	@Override
	public Object getItem(int position) {
		return transactions.get(position);
	}

	@Override
	public long getItemId(int position) {
		return position;
	}
	
	public static class ViewHolder{
        public ImageView transactionImage;
		public TextView transactionDate;
    	public TextView transactionTime;
    	public TextView transactionPrice;
    	public RatingBar transactionPoints;
    	public CheckBox transactionCheckbox;
    }

	@Override
	public View getView(final int position, View convertView, ViewGroup parent) {
		
		View vi=convertView;
		final ViewHolder holder;
		
		if(convertView==null){
			
            vi = inflater.inflate(R.layout.transaction_item, null);
            holder=new ViewHolder();
            holder.transactionImage = (ImageView) vi.findViewById(R.id.transactionImage);
            holder.transactionDate = (TextView) vi.findViewById(R.id.transactionDate);
            holder.transactionTime = (TextView) vi.findViewById(R.id.transactionTime);
            holder.transactionPrice = (TextView) vi.findViewById(R.id.transactionPrice);
            holder.transactionPoints = (RatingBar) vi.findViewById(R.id.transactionPoints);
            holder.transactionCheckbox = (CheckBox) vi.findViewById(R.id.transactionCheckbox);
            
            vi.setTag(holder);
        }
        else{
        	//the current view is being recycled
            holder=(ViewHolder)vi.getTag();
        }
		
		final Transaction transaction = transactions.get(position);

        //set the values
        holder.transactionPrice.setText(Utils.convertPrice(transaction.getPrice()));
        
		//get the time element from the transaction and display it in different elements
		String timestamp = transaction.getTimestamp();
		//format the time and date as needed
		SimpleDateFormat dateTimeFormat = new SimpleDateFormat("dd-MM-yyyy HH:mm:ss");
		SimpleDateFormat timeFormat = new SimpleDateFormat("h:mm a");
		SimpleDateFormat dateFormat = new SimpleDateFormat("EEE, d MMM yyyy");
		//set the time and date according to the desired format
		try{
			holder.transactionDate.setText(Utils.modifyDateLayout(timestamp, dateTimeFormat, dateFormat));
			holder.transactionTime.setText(Utils.modifyDateLayout(timestamp, dateTimeFormat, timeFormat));
		}
		catch (Exception e) {
			Log.e("Date time conversion error", e.getMessage());
		}

        holder.transactionPoints.setProgress(transaction.getPoints());

        String type = transaction.getType();
        if(type.equals("goods")){
            holder.transactionImage.setImageDrawable(vi.getResources().getDrawable(R.drawable.goods_icon_on));
        }
        else if(type.equals("services")){
            holder.transactionImage.setImageDrawable(vi.getResources().getDrawable(R.drawable.service_trans_icon_on));
        }
        else if(type.equals("both")){
            holder.transactionImage.setImageDrawable(vi.getResources().getDrawable(R.drawable.goods_service));
        }

		holder.transactionCheckbox.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				CheckBox cb = (CheckBox) v.findViewById(R.id.transactionCheckbox);
				if(cb.isChecked()){
					checkedBoxes.set(position, false);
					Log.i("Log", "Checkbox "+ position + " - " + transaction.getPrice() + " has been checked");
					transaction.setCheckboxChecked(true);
				}
				else{
					Log.i("Log", "Checkbox "+ position + " - " +  transaction.getPrice() + " has been un-checked");
					transaction.setCheckboxChecked(false);
				}
			}
		});

		holder.transactionCheckbox.setChecked(transaction.isCheckboxChecked());

		return vi;
	}
}