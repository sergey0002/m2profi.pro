<style type="text/css">
	.car {
		padding: 0;
		padding-top: 25px;
		position: absolute;
		left: 0px;
		top: 0px;
		margin: 0;
		width:32px;
	    height:85px;
		text-align: center;  
		background-size: contain; 
	    background-repeat: no-repeat;
	    font-family: "Exo2", sans-serif;
		cursor:pointer;
		display:block;
      }
	  
	  .car:hover{opacity:0.6;}
	  .car img {width:100%;}
	  .pk_num
	  {
		width: 100%;
		text-align: center;
		display: block;
		font-size: 14px;
		font-weight: bold;
	  }
	  .pk_price
	  {
		width: 100%;
		text-align: center;
		display: block;
		font-size: 7px;
		display:none;
	  }
	  .pk_area
	  {
		width: 100%;
		text-align: center;
		display: block;
		font-size: 8px;
		display:none;
	  }
	  .ui-rotatable-handle{ }
	  .car_g{background-image:url('https://' . $GLOBALS['config']['domain'] . '/'sahmatka/parking/car_g.png'); }
	  .car_r{background-image:url('https://' . $GLOBALS['config']['domain'] . '/'sahmatka/parking/car_r.png'); }
	  .car_y{background-image:url('https://' . $GLOBALS['config']['domain'] . '/'sahmatka/parking/car_y.png'); }
	  .car_f{background-image:url('https://' . $GLOBALS['config']['domain'] . '/'sahmatka/parking/car_f.png'); }
	  .car_b{background-image:url('https://' . $GLOBALS['config']['domain'] . '/'sahmatka/parking/car_b.png'); }
	  .ui-rotatable-handle{position:absolute; width:5px; height:5px;     bottom: -7px;     left: 0;}
	  
	  
	  
	  
.objects-head-status-list li {
    position: relative;
    margin-bottom: 5px;
    padding-left: 28px;
    font-size: 12px;
}

.objects-head-status-list li:before {
    content: '';
    position: absolute;
    top: 2px;
    left: 0;
    width: 14px;
    height: 12px;
    border-radius: 3px;
}
	  
 .objects-head-status__green:before {
    background: #8DFFA9;
}


.objects-head-status__yellow:before {
    background: #FEFF52;
}

.objects-head-status__red:before {
    background: #FF8A90;
}

    </style>