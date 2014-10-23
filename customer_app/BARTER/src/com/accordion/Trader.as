package com.accordion
{
	import flash.events.EventDispatcher;
	
	public class Trader extends EventDispatcher
	{
		[Bindable]public var name:String;
		[Bindable]public var tagline:String;
		[Bindable]public var phone:String;
		[Bindable]public var color:uint;
		[Bindable]public var card_offset:int;
		[Bindable]public var photo:String;
		[Bindable]public var trader_type:String;
	}
}