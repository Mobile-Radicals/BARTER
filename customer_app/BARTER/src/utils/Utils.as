package utils
{
	import mx.collections.ArrayCollection;
	import mx.core.FlexGlobals;
	
	public class Utils
	{
		public function Utils(){}

		public static function resetActionBarColor():void{
			config.actionBarColor = 0x85BD40;	
		}
		
		private static function findObjectByAttribute(arrayCollection:ArrayCollection, attributeName:String, value:String):Object{
			for each (var object:Object in arrayCollection) {
				if(object[attributeName]==value){
					return object;
				}
			}
			return null;
		}
		
		public static function getActionBarPos():Number{
			var abPos:Number = 0 ;
			switch(FlexGlobals.topLevelApplication.runtimeDPI){
				case 160:
					abPos = config.actionBarHeight;
					break;
				case 240:
					abPos = config.actionBarHeight * 1.5;
					break;
				case 320:
					abPos = config.actionBarHeight * 2;
					break;
				case 480:
					abPos = config.actionBarHeight * 3;
					break;
			}
			return abPos;
		}
			
	}
}