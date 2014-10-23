// CUSTOM EVENT
package
{
	import flash.events.Event;
	
	public class CustomEvent extends Event
	{
		public static const UPDATE:String   = "CLICK";
		public var data: Object;
		
		public function CustomEvent(type:String, data:Object, bubbles:Boolean=false, cancelable:Boolean=false)   
		{
			this.data = data;
			super(type, bubbles, cancelable);
		}
		
		override public function clone():Event
		{
			return new CustomEvent(type, data);
		}
	}
}