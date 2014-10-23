package skins
{
	import flash.display.InterpolationMethod;
	import flash.display.SpreadMethod;
	import flash.geom.Matrix;
	
	import spark.skins.mobile.ActionBarSkin;
	
	public class CustomSkinningActionBar extends ActionBarSkin
	{
		public function CustomSkinningActionBar()
		{
			super();
		}
		
		override protected function drawBackground(unscaledWidth:Number, unscaledHeight:Number):void{
			
			//define the type of the gradient
			var fillType:String = "linear";
			//define the transparency
			var alphas:Array = [100, 100];
			//ratio for each colour used
			var ratios:Array = [0, 0xFF];
			
			var spreadMethod:String = SpreadMethod.PAD;
			var interp:String = InterpolationMethod.LINEAR_RGB;
			var focalPtRatio:Number = 0;
			//create the desired matrix to lay out the colours for the background
			var matrix:Matrix = new Matrix();
			var boxWidth:Number = this.width;
			
			var boxHeight:Number = this.height;
			var boxRotation:Number = Math.PI/2; //90
			var tx:Number = 0;
			var ty:Number = 0;
			matrix.createGradientBox(boxWidth, boxHeight, boxRotation, tx, ty);
			
			graphics.beginFill(config.actionBarColor,1);
			graphics.drawRect(0, 0, unscaledWidth, unscaledHeight); //position of the surrounding box
			graphics.endFill(); //stop colouring
			
		}
	}
}