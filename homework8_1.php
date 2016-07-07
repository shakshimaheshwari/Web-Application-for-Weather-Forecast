<!DOCTYPE html>
<html lang="en">
<head>
  <title>Forecast Website</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
    <script src="http://openlayers.org/api/OpenLayers.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.4.1/moment-timezone.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.4.0/moment-timezone-with-data-2010-2020.js"></script>
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  
    <style type = "text/css">
      body
      {
        background-image:url("http://cs-server.usc.edu:45678/hw/hw8/images/bg.jpg");
      }
        
      .btn-default
        {
             background: #0099cc;
             color: #ffffff;
        }
        
        .nav-pills a
        {
            background-color:white;
        }
         #right
       {
          height: 450px;
          border: 2px solid white;
       }
        
         #Next24hrshead
      {
          background-color: #0099cc; 
          color:white;
      }
      
      #Next24hrsbody
      {
          background-color: white;
      }
    
        .well
        {
            background: rgb(0,0,0);
            background: rgba(0,0,0,0.1);
            border: rgba(0,0,0,0.1);
            
        }
        #summaryheader
        {
            color:white;
            text-align: center;
            font-weight:bold;
        }
        #temperature
        {
            font-size: 40px;
            color:white;
            text-align:center;  
        }
        #temperatureMin
        {
            color:blue;
            text-align:center;
        }
        #tempMax
        {
            color:green;
        }
        #bar1
        {
            color:black;
        }
        #tempSym
        {
            font-size: 20px;
            color:white;
        }
        .table-striped > tbody > tr:nth-of-type(even){ background-color: pink;}
    </style>
    <script>
        

        
        
//Reseting the contents on press of Reset button
        
function ResetContents()
        {
                $("#parseddata").hide(0);
        }

$(document).ready(function() {

$('#QueryForm').validate({
           rules: {
               streetAdd: "required",
               cityname: "required",
               StateName:{
                   selectcheck: true
               }
           },
            messages:{
                
                streetAdd: "Please enter the street address",
                cityname: "Please enter the city name",
            },
            errorPlacement: function(error, element)
            {
                if(element.attr("name") == "streetAdd")
                error.insertAfter("#error1");
                if(element.attr("name") == "cityname")
                error.insertBefore("#error2");
                if(element.attr("name") == "StateName")
                error.insertAfter("#error3");
                error.css('color','red');
            }
               
        });
    
    jQuery.validator.addMethod('selectcheck',function(value){
        return (value!='SelectName');
    },"Select a state name");
    $("#parseddata").hide(0);
        $('#QueryForm').submit(function()
        {    
        //Retrieve the values of the search boxes 
        streetAddress = $('#streetaddress').val();
        cityname = $('#cityname').val();
        stateName = $('#StateName').val();
        degree = $('input[name = "radioGroup"]:checked','#QueryForm').val();
        $.ajax({

            url :"http://forecast-prediction.elasticbeanstalk.com/",
            cache: false,
            async: true,
            method: "GET",
            datatype:"json",
            data: {streetAdd: streetAddress, cityname: cityname, StateName: stateName, radiogroup: degree},

            success: function(data)
            {
                $("div#right").empty();
                var json_data = JSON.parse(data);
                $("#parseddata").show();
                summary = json_data.currently.summary;
                var icon =json_data.currently.icon;
                var temperaturecurrently = json_data.currently.temperature;
                var precipitationvalue = json_data.currently.precipIntensity;
                var summarytext = " ";
                var modaltext1 = " ";
                var tempMinHeader = json_data.daily.data[0].temperatureMin;
                var tempMaxHeader =json_data.daily.data[0].temperatureMax;
                
                sunrise = json_data.daily.data[0].sunriseTime;
                sunset = json_data.daily.data[1].sunsetTime;

                var toff = json_data.offset;
                var off = moment().utcOffset();
                toff=(toff)*60-off;
                rise =moment(sunrise*1000+(toff*60000)).format('hh:mm');
                set = moment(sunset*1000+(toff*60000)).format('hh:mm');
                summarytext+= summary+" "+"in"+" "+ cityname +","+ stateName;
                if(degree == "Celcius")
                {
                    precipitationvalue = ((precipitationvalue)/24.5);
                }
                if(precipitationvalue>= 0 && precipitationvalue <0.002)
                {
                    value = "None";
                }
                else if(precipitationvalue >= 0.002 && precipitationvalue <0.017)
                {
                    value = "Very light";
                }
                else if(precipitationvalue >=0.017 && precipitationvalue <0.1 )
                {
                    value = "Light";
                }

                else if( precipitationvalue >= 0.1 && precipitationvalue <0.4)
                {
                    value = "Moderate";

                }
                else if(precipitationvalue >=0.4)
                {
                    value = "Heavy";
                }


                chanceofrain = json_data.currently.precipProbability;
                chanceofraininpercentage= (chanceofrain*100);
                windspeed = json_data.currently.windSpeed;
                dewpoint = json_data.currently.dewPoint;
                humidity= json_data.currently.humidity;
                visibility = json_data.currently.visibility;
                
                //Map creation
                timezoneused = json_data.timezone;
                latitude = json_data.latitude;
                longitude = json_data.longitude;
                //var lonlat = new OpenLayers.LonLat(longitude, latitude);

                    var map = new OpenLayers.Map("right");
                    // Create OSM overlays
                    var mapnik = new OpenLayers.Layer.OSM();

                    var layer_cloud = new OpenLayers.Layer.XYZ(
                    "clouds",
                    "http://${s}.tile.openweathermap.org/map/clouds/${z}/${x}/${y}.png",
                    {
                        isBaseLayer: false,
                        opacity: 0.7,
                        sphericalMercator: true
                    }
                );

                var layer_precipitation = new OpenLayers.Layer.XYZ(
                    "precipitation",
                    "http://${s}.tile.openweathermap.org/map/precipitation/${z}/${x}/${y}.png",
                    {
                        isBaseLayer: false,
                        opacity: 0.7,
                        sphericalMercator: true
                    }
                );
                map.addLayers([mapnik, layer_precipitation, layer_cloud]);
                map.addControl(new OpenLayers.Control.LayerSwitcher());     
    
                var lonlat = new OpenLayers.LonLat(longitude, latitude).transform(
                    new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
                    map.getProjectionObject() // to Spherical Mercator Projection
                  );
                map.setCenter( lonlat, 7 );		

                var markers = new OpenLayers.Layer.Markers( "Markers" );
                map.addLayer(markers);
                markers.addMarker(new OpenLayers.Marker(lonlat));
                
                //Map creation ended
                
            //Next 24hrs and Next 7 days data
                
            var cloudcover = [];
        var temperature = [];
        var visibilitydaily = [];
        var pressure = [];
        var humiditydaily = [];
        var windspeeddaily = [];
        var icondaily = [];
        var imagedaily = [];
        var iconimagedaily = [];
        var iconimageweekly = [];
        var iconweekly = [];
        var imageweekly = [];
        var tempMinWeekly = [];
        var tempMaxWeekly = [];
        var iconweeklymodal =[];
        var imageweeklymodal =[];
        var iconimageweeklymodal=[];
        var summarydailymodal =[];
        var humiditymodal=[];
        var windSpeedmodal = [];
        var visibilitymodal = [];
        var pressuremodal = [];
        var timedaily = [];
        var timedailyconverted = [];
        var sunriseTimemodal = [];
        var sunriseTimemodalConverted = [];
        var sunsetTimemodal = [];
        var sunsetTimemodalConverted =[];
        var Monthmodal = [];
        var MonthModalConverted =[];
        var DateModal =[];
        var Daymodal = [];
        
        for(t=1;t<8;t++)
        {
            tempMinWeekly[t] = json_data.daily.data[t].temperatureMin;
            tempMaxWeekly[t] = json_data.daily.data[t].temperatureMax;
            summarydailymodal[t]= json_data.daily.data[t].summary;
            humiditymodal[t] = json_data.daily.data[t].humidity;
            windSpeedmodal[t]= json_data.daily.data[t].windSpeed;
            visibilitymodal[t]= json_data.daily.data[t].visibility;
            pressuremodal[t] = json_data.daily.data[t].pressure;
            sunriseTimemodal[t] = json_data.daily.data[t].sunriseTime;
            sunriseTimemodalConverted[t] = moment(sunriseTimemodal[t]*1000+(toff*60000)).format('hh:mm');
            sunsetTimemodal[t] = json_data.daily.data[t].sunsetTime;
            sunsetTimemodalConverted[t] = moment(sunsetTimemodal[t]*1000+(toff*60000)).format('hh:mm');
            Monthmodal[t]= json_data.daily.data[t].time;
            MonthModalConverted[t]= moment(Monthmodal[t]*1000+(toff*60000)).format('MMM');
            DateModal[t]= moment(Monthmodal[t]*1000+(toff*60000)).format('DD');
            Daymodal[t]= moment(Monthmodal[t]*1000+(toff*60000)).format('dddd');
            
        }
        for( k=1;k<8;k++)
        {
            iconweekly[k] = json_data.daily.data[k].icon;
            imageweekly[k]=" ";
             iconimageweekly[k]=" ";
             switch (iconweekly[k])
            {
                case "clear-day": iconimageweekly[k] = "clear.png";
                            break;

                case "clear-night": iconimageweekly[k] = "clear_night.png";
                            break;

                case "rain": iconimageweekly[k] = "rain.png";
                            break;

                case "snow": iconimageweekly[k] = "snow.png";
                            break;

                case "sleet": iconimageweekly[k] = "sleet.png";
                            break;

                case "wind": iconimageweekly[k] = "wind.png";
                            break;

                case "fog": iconimageweekly[k] = "fog.png";
                            break;

                case "cloudy": iconimageweekly[k] = "cloudy.png";
                            break;

                case "partly-cloudy-day": iconimageweekly[k] = "cloud_day.png";
                            break;

                case "partly-cloudy-night":iconimageweekly[k] ="cloud_night.png";
                            break;

            }
            
            imageweekly[k] += "<img src ="+"http://cs-server.usc.edu:45678/hw/hw8/images/"+iconimageweekly[k]+" height = 50px width = 30px>";
        }
        
        //For modal window
        
           for( k=1;k<8;k++)
        {
            iconweeklymodal[k] = json_data.daily.data[k].icon;
            imageweeklymodal[k]=" ";
             iconimageweeklymodal[k]=" ";
             switch (iconweeklymodal[k])
            {
                case "clear-day": iconimageweeklymodal[k] = "clear.png";
                            break;

                case "clear-night": iconimageweeklymodal[k] = "clear_night.png";
                            break;

                case "rain": iconimageweeklymodal[k] = "rain.png";
                            break;

                case "snow": iconimageweeklymodal[k] = "snow.png";
                            break;

                case "sleet": iconimageweeklymodal[k] = "sleet.png";
                            break;

                case "wind": iconimageweeklymodal[k] = "wind.png";
                            break;

                case "fog": iconimageweeklymodal[k] = "fog.png";
                            break;

                case "cloudy": iconimageweeklymodal[k] = "cloudy.png";
                            break;

                case "partly-cloudy-day": iconimageweeklymodal[k] = "cloud_day.png";
                            break;

                case "partly-cloudy-night":iconimageweeklymodal[k] ="cloud_night.png";
                            break;

            }
            
            imageweeklymodal[k] += "<img src ="+"http://cs-server.usc.edu:45678/hw/hw8/images/"+iconimageweeklymodal[k]+" height = 200px width = 150px >";
        }
        
       /* var fbimg = " ";
        fbimg+= "<div style="+"'position:relative;width:27px;height:27px;'"+">"+
        "<div style="+"'position:absolute;left:0px;top:0px;width:27px;height:27px;z-index:111;'"+">"+
        "<img src="+"'http://cs-server.usc.edu:37430/Homework8/fb_icon-Copy.png'"+"class="+"'img-responsive'"+"style="+"'cursor:pointer;display:inline-block;'"+"/>"+
        "</div>"+
        "<div style="+"'position:absolute;left:0px;top:0px;width:27px;height:27px;overflow:hidden;z-index:333;opacity:0;filter:alpha(opacity=0);'"+">"+
"<fb:login-button "+"width=27px"+ " height=27px" +" scope=" + "public_profile,email onlogin="+"'checkLoginState();'"+">"+
"</fb:login-button>"+
"</div>"+
"</div>";*/;
        for( i=0;i<24;i++)
        {
        cloudcover[i]= json_data.hourly.data[i].cloudCover;
        temperature[i] = json_data.hourly.data[i].temperature;
        windspeeddaily[i] = json_data.hourly.data[i].windSpeed;
        humiditydaily[i] = json_data.hourly.data[i].humidity;
        visibilitydaily[i]=json_data.hourly.data[i].visibility;
        pressure[i]=json_data.hourly.data[i].pressure;
        timedaily[i]=json_data.hourly.data[i].time;
        timedailyconverted[i] =moment(timedaily[i]*1000+(toff*60000)).format('hh:mm A');
        }
        
        for(j=0;j<24;j++)
        {
             icondaily[j] = json_data.hourly.data[j].icon;
             imagedaily[j]=" ";
             iconimagedaily[j]=" ";
             switch (icondaily[j])
            {
                case "clear-day": iconimagedaily[j] = "clear.png";
                            break;

                case "clear-night": iconimagedaily[j] = "clear_night.png";
                            break;

                case "rain": iconimagedaily[j] = "rain.png";
                            break;

                case "snow": iconimagedaily[j] = "snow.png";
                            break;

                case "sleet": iconimagedaily[j] = "sleet.png";
                            break;

                case "wind": iconimagedaily[j] = "wind.png";
                            break;

                case "fog": iconimagedaily[j] = "fog.png";
                            break; 

                case "cloudy": iconimagedaily[j] = "cloudy.png";
                            break;

                case "partly-cloudy-day": iconimagedaily[j] = "cloud_day.png";
                            break;

                case "partly-cloudy-night":iconimagedaily[j] ="cloud_night.png";
                            break;

            }
            
            imagedaily[j] += "<img src ="+"http://cs-server.usc.edu:45678/hw/hw8/images/"+iconimagedaily[j]+" height = 50px width = 30px>";
        }
                
        //<!-- Finding the sunrise and sunset time-->
       /*var toff=data.offset;
        var off=moment().utcOffset();
                toff=(toff)*60-off;
                rise=moment(rise*1000+(toff*60000)).format('hh:mm');
                setnow=moment(setnow*1000+(toff*60000)).format('hh:mm');
                
               $("#risenow").html(rise+' AM');
               $("#setnow").html(setnow+' PM');*/

      
                
        /*var rise = moment.unix(sunrise).format("YYYY-MM-DD hh:mm");
        var sym =  moment.unix(sunrise).format("A");
        var set =  moment.unix(sunrise).format("YYYY-MM-DD hh:mm");*/
        
        
        switch (icon)
        {
            case "clear-day": icon_image = "clear.png";
                        break;

            case "clear-night": icon_image = "clear_night.png";
                        break;

            case "rain": icon_image = "rain.png";
                        break;

            case "snow": icon_image = "snow.png";
                        break;

            case "sleet": icon_image = "sleet.png";
                        break;

            case "wind": icon_image = "wind.png";
                        break;

            case "fog": icon_image = "fog.png";
                        break;

            case "cloudy": icon_image = "cloudy.png";
                        break;

            case "partly-cloudy-day": icon_image = "cloud_day.png";
                        break;

            case "partly-cloudy-night":icon_image ="cloud_night.png";
                        break;

        }
                
            //Printing the values
            //Tab 1 values printed
            text =" ";
            text+= "<img src ="+" http://cs-server.usc.edu:45678/hw/hw8/images/"+icon_image+" height = 110px width = 80px alt ="+summary+" title = "+summary+" tooltip="+summary+">";
            document.getElementById("imageicon").innerHTML = text;
            document.getElementById("precipitationvalue").innerHTML = value;
            document.getElementById("chancerain").innerHTML = chanceofraininpercentage+"%";
            if(degree == "Farenheit")
            {
                document.getElementById("wind").innerHTML = windspeed.toFixed(2)+"mph";
                document.getElementById("dewpoint").innerHTML = dewpoint.toFixed(2)+"&deg;F";
                document.getElementById("visibility").innerHTML = visibility.toFixed(2)+"mi";
            }
            else
            {
                document.getElementById("wind").innerHTML = windspeed.toFixed(2)+"m/s";
                document.getElementById("dewpoint").innerHTML = dewpoint.toFixed(2)+"&deg;C";
                document.getElementById("visibility").innerHTML = visibility.toFixed(2)+"km";
            }
            
            document.getElementById("humidity").innerHTML = Math.round(humidity)+"%";
            var tempSymbol;
            if(degree == "Farenheit")
            {
                tempSymbol = "F";
            }
            else
            {
                tempSymbol = "C";
            }
            document.getElementById("sunrisetime").innerHTML = rise+"AM";
            document.getElementById("sunsettime").innerHTML = set+"PM";
            document.getElementById("clubbedheaderdata").innerHTML = "<p id = "+"summaryheader"+">"+summarytext+"</p>"+"<p id="+"temperature"+">"+Math.round(temperaturecurrently)+"&deg"+"<span id ="+"tempSym"+">"+"<sup>"+tempSymbol+"</sup></span></p>"+ "<p id="+"temperatureMin"+">"+"L:"+Math.round(tempMinHeader)+"&deg"+"<span id = "+"bar1"+">"+"|"+"</span>"+"<span id ="+"tempMax"+">"+"H:"+Math.round(tempMaxHeader)+"&deg"+"</span></p>";
        
            //Tab 1 values end
                
           /*Tab 2 values*/
        document.getElementById("timedaily0").innerHTML = timedailyconverted[0];
        document.getElementById("timedaily1").innerHTML = timedailyconverted[1]; 
        document.getElementById("timedaily2").innerHTML = timedailyconverted[2]; 
        document.getElementById("timedaily3").innerHTML = timedailyconverted[3]; 
        document.getElementById("timedaily4").innerHTML = timedailyconverted[4]; 
        document.getElementById("timedaily5").innerHTML = timedailyconverted[5]; 
        document.getElementById("timedaily6").innerHTML = timedailyconverted[6]; 
        document.getElementById("timedaily7").innerHTML = timedailyconverted[7];
        document.getElementById("timedaily8").innerHTML = timedailyconverted[8];
        document.getElementById("timedaily9").innerHTML = timedailyconverted[9]; 
        document.getElementById("timedaily10").innerHTML = timedailyconverted[10]; 
        document.getElementById("timedaily11").innerHTML = timedailyconverted[11]; 
        document.getElementById("timedaily12").innerHTML = timedailyconverted[12]; 
        document.getElementById("timedaily13").innerHTML = timedailyconverted[13]; 
        document.getElementById("timedaily14").innerHTML = timedailyconverted[14]; 
        document.getElementById("timedaily15").innerHTML = timedailyconverted[15];
        document.getElementById("timedaily16").innerHTML = timedailyconverted[16];
        document.getElementById("timedaily17").innerHTML = timedailyconverted[17]; 
        document.getElementById("timedaily18").innerHTML = timedailyconverted[18]; 
        document.getElementById("timedaily19").innerHTML = timedailyconverted[19]; 
        document.getElementById("timedaily20").innerHTML = timedailyconverted[20]; 
        document.getElementById("timedaily21").innerHTML = timedailyconverted[21]; 
        document.getElementById("timedaily22").innerHTML = timedailyconverted[22]; 
        document.getElementById("timedaily23").innerHTML = timedailyconverted[23];
                
        document.getElementById("Summary0").innerHTML = imagedaily[0];
        document.getElementById("Summary1").innerHTML = imagedaily[1];
        document.getElementById("Summary2").innerHTML = imagedaily[2];
        document.getElementById("Summary3").innerHTML = imagedaily[3];
        document.getElementById("Summary4").innerHTML = imagedaily[4];
        document.getElementById("Summary5").innerHTML = imagedaily[5];
        document.getElementById("Summary6").innerHTML = imagedaily[6];
        document.getElementById("Summary7").innerHTML = imagedaily[7];
        document.getElementById("Summary8").innerHTML = imagedaily[8];
        document.getElementById("Summary9").innerHTML = imagedaily[9];
        document.getElementById("Summary10").innerHTML = imagedaily[10];
        document.getElementById("Summary11").innerHTML = imagedaily[11];
        document.getElementById("Summary12").innerHTML = imagedaily[12];
        document.getElementById("Summary13").innerHTML = imagedaily[13];
        document.getElementById("Summary14").innerHTML = imagedaily[14];
        document.getElementById("Summary15").innerHTML = imagedaily[15];
        document.getElementById("Summary16").innerHTML = imagedaily[16];
        document.getElementById("Summary17").innerHTML = imagedaily[17];
        document.getElementById("Summary18").innerHTML = imagedaily[18];
        document.getElementById("Summary19").innerHTML = imagedaily[19];
        document.getElementById("Summary20").innerHTML = imagedaily[20];
        document.getElementById("Summary21").innerHTML = imagedaily[21];
        document.getElementById("Summary22").innerHTML = imagedaily[22];
        document.getElementById("Summary23").innerHTML = imagedaily[23];
        
        
        document.getElementById("cloudcover0").innerHTML = (cloudcover[0]*100).toFixed(0)+"%";
        document.getElementById("temperature0").innerHTML = temperature[0];
        document.getElementById("cloudcover1").innerHTML = (cloudcover[1]*100).toFixed(0)+"%";
        document.getElementById("temperature1").innerHTML = temperature[1];
        document.getElementById("cloudcover2").innerHTML = (cloudcover[2]*100).toFixed(0)+"%";
        document.getElementById("temperature2").innerHTML = temperature[2];
        document.getElementById("cloudcover3").innerHTML = (cloudcover[3]*100).toFixed(0)+"%";
        document.getElementById("temperature3").innerHTML = temperature[3];
        document.getElementById("cloudcover4").innerHTML = (cloudcover[4]*100).toFixed(0)+"%";
        document.getElementById("temperature4").innerHTML = temperature[4];
        document.getElementById("cloudcover5").innerHTML = (cloudcover[5]*100).toFixed(0)+"%";
        document.getElementById("temperature5").innerHTML = temperature[5];
        document.getElementById("cloudcover6").innerHTML = (cloudcover[6]*100).toFixed(0)+"%";
        document.getElementById("temperature6").innerHTML = temperature[6];
        document.getElementById("cloudcover7").innerHTML = (cloudcover[7]*100).toFixed(0)+"%";
        document.getElementById("temperature7").innerHTML = temperature[7];
        document.getElementById("cloudcover8").innerHTML = (cloudcover[8]*100).toFixed(0)+"%";
        document.getElementById("temperature8").innerHTML = temperature[8];
        document.getElementById("cloudcover9").innerHTML = (cloudcover[9]*100).toFixed(0)+"%";
        document.getElementById("temperature9").innerHTML = temperature[9];
        document.getElementById("cloudcover10").innerHTML =(cloudcover[10]*100).toFixed(0)+"%";
        document.getElementById("temperature10").innerHTML = temperature[10];
        document.getElementById("cloudcover11").innerHTML = (cloudcover[11]*100).toFixed(0)+"%";
        document.getElementById("temperature11").innerHTML = temperature[11];
        document.getElementById("cloudcover12").innerHTML = (cloudcover[12]*100).toFixed(0)+"%";
        document.getElementById("temperature12").innerHTML = temperature[12];
        document.getElementById("cloudcover13").innerHTML = (cloudcover[13]*100).toFixed(0)+"%";
        document.getElementById("temperature13").innerHTML = temperature[13];
        document.getElementById("cloudcover14").innerHTML = (cloudcover[14]*100).toFixed(0)+"%";
        document.getElementById("temperature14").innerHTML = temperature[14];
        document.getElementById("cloudcover15").innerHTML = (cloudcover[15]*100).toFixed(0)+"%";
        document.getElementById("temperature15").innerHTML = temperature[15];
        document.getElementById("cloudcover16").innerHTML = (cloudcover[16]*100).toFixed(0)+"%";
        document.getElementById("temperature16").innerHTML = temperature[16];
        document.getElementById("cloudcover17").innerHTML = (cloudcover[17]*100).toFixed(0)+"%";
        document.getElementById("temperature17").innerHTML = temperature[17];
        document.getElementById("cloudcover18").innerHTML = (cloudcover[18]*100).toFixed(0)+"%";
        document.getElementById("temperature18").innerHTML = temperature[18];
        document.getElementById("cloudcover19").innerHTML = (cloudcover[19]*100).toFixed(0)+"%";
        document.getElementById("temperature19").innerHTML = temperature[19];
        document.getElementById("cloudcover20").innerHTML = (cloudcover[20]*100).toFixed(0)+"%";
        document.getElementById("temperature20").innerHTML = temperature[20];
        document.getElementById("cloudcover21").innerHTML = (cloudcover[21]*100).toFixed(0)+"%";
        document.getElementById("temperature21").innerHTML = temperature[21];
        document.getElementById("cloudcover22").innerHTML = (cloudcover[22]*100).toFixed(0)+"%";
        document.getElementById("temperature22").innerHTML = temperature[22];
        document.getElementById("cloudcover23").innerHTML = (cloudcover[23]*100).toFixed(0)+"%";
        document.getElementById("temperature23").innerHTML = temperature[23];
                
                
                
       if(degree == "Farenheit")
       {
            document.getElementById("windspeed0").innerHTML = windspeeddaily[0]+"mph";
            document.getElementById("windspeed1").innerHTML = windspeeddaily[1]+"mph";
            document.getElementById("windspeed2").innerHTML = windspeeddaily[2]+"mph";
            document.getElementById("windspeed3").innerHTML = windspeeddaily[3]+"mph";
            document.getElementById("windspeed4").innerHTML = windspeeddaily[4]+"mph";
            document.getElementById("windspeed5").innerHTML = windspeeddaily[5]+"mph";
            document.getElementById("windspeed6").innerHTML = windspeeddaily[6]+"mph";
            document.getElementById("windspeed7").innerHTML = windspeeddaily[7]+"mph";
            document.getElementById("windspeed8").innerHTML = windspeeddaily[8]+"mph";
            document.getElementById("windspeed9").innerHTML = windspeeddaily[9]+"mph";
            document.getElementById("windspeed10").innerHTML = windspeeddaily[10]+"mph";
            document.getElementById("windspeed11").innerHTML = windspeeddaily[11]+"mph";
            document.getElementById("windspeed12").innerHTML = windspeeddaily[12]+"mph";
            document.getElementById("windspeed13").innerHTML = windspeeddaily[13]+"mph";
            document.getElementById("windspeed14").innerHTML = windspeeddaily[14]+"mph";
            document.getElementById("windspeed15").innerHTML = windspeeddaily[15]+"mph";
            document.getElementById("windspeed16").innerHTML = windspeeddaily[16]+"mph";
            document.getElementById("windspeed17").innerHTML = windspeeddaily[17]+"mph";
            document.getElementById("windspeed18").innerHTML = windspeeddaily[18]+"mph";
            document.getElementById("windspeed19").innerHTML = windspeeddaily[19]+"mph";
            document.getElementById("windspeed20").innerHTML = windspeeddaily[20]+"mph";
            document.getElementById("windspeed21").innerHTML = windspeeddaily[21]+"mph";
            document.getElementById("windspeed22").innerHTML = windspeeddaily[22]+"mph";
            document.getElementById("windspeed23").innerHTML = windspeeddaily[23]+"mph";
       }
        else
        {
            document.getElementById("windspeed0").innerHTML = windspeeddaily[0]+"m/s";
            document.getElementById("windspeed1").innerHTML = windspeeddaily[1]+"m/s";
            document.getElementById("windspeed2").innerHTML = windspeeddaily[2]+"m/s";
            document.getElementById("windspeed3").innerHTML = windspeeddaily[3]+"m/s";
            document.getElementById("windspeed4").innerHTML = windspeeddaily[4]+"m/s";
            document.getElementById("windspeed5").innerHTML = windspeeddaily[5]+"m/s";
            document.getElementById("windspeed6").innerHTML = windspeeddaily[6]+"m/s";
            document.getElementById("windspeed7").innerHTML = windspeeddaily[7]+"m/s";
            document.getElementById("windspeed8").innerHTML = windspeeddaily[8]+"m/s";
            document.getElementById("windspeed9").innerHTML = windspeeddaily[9]+"m/s";
            document.getElementById("windspeed10").innerHTML = windspeeddaily[10]+"m/s";
            document.getElementById("windspeed11").innerHTML = windspeeddaily[11]+"m/s";
            document.getElementById("windspeed12").innerHTML = windspeeddaily[12]+"m/s";
            document.getElementById("windspeed13").innerHTML = windspeeddaily[13]+"m/s";
            document.getElementById("windspeed14").innerHTML = windspeeddaily[14]+"m/s";
            document.getElementById("windspeed15").innerHTML = windspeeddaily[15]+"m/s";
            document.getElementById("windspeed16").innerHTML = windspeeddaily[16]+"m/s";
            document.getElementById("windspeed17").innerHTML = windspeeddaily[17]+"m/s";
            document.getElementById("windspeed18").innerHTML = windspeeddaily[18]+"m/s";
            document.getElementById("windspeed19").innerHTML = windspeeddaily[19]+"m/s";
            document.getElementById("windspeed20").innerHTML = windspeeddaily[20]+"m/s";
            document.getElementById("windspeed21").innerHTML = windspeeddaily[21]+"m/s";
            document.getElementById("windspeed22").innerHTML = windspeeddaily[22]+"m/s";
            document.getElementById("windspeed23").innerHTML = windspeeddaily[23]+"m/s";
        }
        
        document.getElementById("humidity0").innerHTML = humiditydaily[0].toFixed(0)+"%";  
        document.getElementById("humidity1").innerHTML = humiditydaily[1].toFixed(0)+"%";
        document.getElementById("humidity2").innerHTML = humiditydaily[2].toFixed(0)+"%";
        document.getElementById("humidity3").innerHTML = humiditydaily[3].toFixed(0)+"%";
        document.getElementById("humidity4").innerHTML = humiditydaily[4].toFixed(0)+"%";
        document.getElementById("humidity5").innerHTML = humiditydaily[5].toFixed(0)+"%";
        document.getElementById("humidity6").innerHTML = humiditydaily[6].toFixed(0)+"%";
        document.getElementById("humidity7").innerHTML = humiditydaily[7].toFixed(0)+"%";
        document.getElementById("humidity8").innerHTML = humiditydaily[8].toFixed(0)+"%";
        document.getElementById("humidity9").innerHTML = humiditydaily[9].toFixed(0)+"%";
        document.getElementById("humidity10").innerHTML = humiditydaily[10].toFixed(0)+"%";
        document.getElementById("humidity11").innerHTML = humiditydaily[11].toFixed(0)+"%";
        document.getElementById("humidity12").innerHTML = humiditydaily[12].toFixed(0)+"%";
        document.getElementById("humidity13").innerHTML = humiditydaily[13].toFixed(0)+"%";
        document.getElementById("humidity14").innerHTML = humiditydaily[14].toFixed(0)+"%";
        document.getElementById("humidity15").innerHTML = humiditydaily[15].toFixed(0)+"%";
        document.getElementById("humidity16").innerHTML = humiditydaily[16].toFixed(0)+"%";
        document.getElementById("humidity17").innerHTML = humiditydaily[17].toFixed(0)+"%";
        document.getElementById("humidity18").innerHTML = humiditydaily[18].toFixed(0)+"%";
        document.getElementById("humidity19").innerHTML = humiditydaily[19].toFixed(0)+"%";
        document.getElementById("humidity20").innerHTML = humiditydaily[20].toFixed(0)+"%";
        document.getElementById("humidity21").innerHTML = humiditydaily[21].toFixed(0)+"%";
        document.getElementById("humidity22").innerHTML = humiditydaily[22].toFixed(0)+"%";
        document.getElementById("humidity23").innerHTML = humiditydaily[23].toFixed(0)+"%";
        
        if(degree == "Farenheit")
        {
            document.getElementById("visibility0").innerHTML = visibilitydaily[0].toFixed(2)+"mi";
            document.getElementById("visibility1").innerHTML = visibilitydaily[1].toFixed(2)+"mi";
            document.getElementById("visibility2").innerHTML = visibilitydaily[2].toFixed(2)+"mi";
            document.getElementById("visibility3").innerHTML = visibilitydaily[3].toFixed(2)+"mi";
            document.getElementById("visibility4").innerHTML = visibilitydaily[4].toFixed(2)+"mi";
            document.getElementById("visibility5").innerHTML = visibilitydaily[5].toFixed(2)+"mi";
            document.getElementById("visibility6").innerHTML = visibilitydaily[6].toFixed(2)+"mi";
            document.getElementById("visibility7").innerHTML = visibilitydaily[7].toFixed(2)+"mi";
            document.getElementById("visibility8").innerHTML = visibilitydaily[8].toFixed(2)+"mi";
            document.getElementById("visibility9").innerHTML = visibilitydaily[9].toFixed(2)+"mi";
            document.getElementById("visibility10").innerHTML = visibilitydaily[10].toFixed(2)+"mi";
            document.getElementById("visibility11").innerHTML = visibilitydaily[11].toFixed(2)+"mi";
            document.getElementById("visibility12").innerHTML = visibilitydaily[12].toFixed(2)+"mi";
            document.getElementById("visibility13").innerHTML = visibilitydaily[13].toFixed(2)+"mi";
            document.getElementById("visibility14").innerHTML = visibilitydaily[14].toFixed(2)+"mi";
            document.getElementById("visibility15").innerHTML = visibilitydaily[15].toFixed(2)+"mi";
            document.getElementById("visibility16").innerHTML = visibilitydaily[16].toFixed(2)+"mi";
            document.getElementById("visibility17").innerHTML = visibilitydaily[17].toFixed(2)+"mi";
            document.getElementById("visibility18").innerHTML = visibilitydaily[18].toFixed(2)+"mi";
            document.getElementById("visibility19").innerHTML = visibilitydaily[19].toFixed(2)+"mi";
            document.getElementById("visibility20").innerHTML = visibilitydaily[20].toFixed(2)+"mi";
            document.getElementById("visibility21").innerHTML = visibilitydaily[21].toFixed(2)+"mi";
            document.getElementById("visibility22").innerHTML = visibilitydaily[22].toFixed(2)+"mi";
            document.getElementById("visibility23").innerHTML = visibilitydaily[23].toFixed(2)+"mi";
        }
        else
        {
            document.getElementById("visibility0").innerHTML = visibilitydaily[0].toFixed(2)+"km";
            document.getElementById("visibility1").innerHTML = visibilitydaily[1].toFixed(2)+"km";
            document.getElementById("visibility2").innerHTML = visibilitydaily[2].toFixed(2)+"km";
            document.getElementById("visibility3").innerHTML = visibilitydaily[3].toFixed(2)+"km";
            document.getElementById("visibility4").innerHTML = visibilitydaily[4].toFixed(2)+"km";
            document.getElementById("visibility5").innerHTML = visibilitydaily[5].toFixed(2)+"km";
            document.getElementById("visibility6").innerHTML = visibilitydaily[6].toFixed(2)+"km";
            document.getElementById("visibility7").innerHTML = visibilitydaily[7].toFixed(2)+"km";
            document.getElementById("visibility8").innerHTML = visibilitydaily[8].toFixed(2)+"km";
            document.getElementById("visibility9").innerHTML = visibilitydaily[9].toFixed(2)+"km";
            document.getElementById("visibility10").innerHTML = visibilitydaily[10].toFixed(2)+"km";
            document.getElementById("visibility11").innerHTML = visibilitydaily[11].toFixed(2)+"km";
            document.getElementById("visibility12").innerHTML = visibilitydaily[12].toFixed(2)+"km";
            document.getElementById("visibility13").innerHTML = visibilitydaily[13].toFixed(2)+"km";
            document.getElementById("visibility14").innerHTML = visibilitydaily[14].toFixed(2)+"km";
            document.getElementById("visibility15").innerHTML = visibilitydaily[15].toFixed(2)+"km";
            document.getElementById("visibility16").innerHTML = visibilitydaily[16].toFixed(2)+"km";
            document.getElementById("visibility17").innerHTML = visibilitydaily[17].toFixed(2)+"km";
            document.getElementById("visibility18").innerHTML = visibilitydaily[18].toFixed(2)+"km";
            document.getElementById("visibility19").innerHTML = visibilitydaily[19].toFixed(2)+"km";
            document.getElementById("visibility20").innerHTML = visibilitydaily[20].toFixed(2)+"km";
            document.getElementById("visibility21").innerHTML = visibilitydaily[21].toFixed(2)+"km";
            document.getElementById("visibility22").innerHTML = visibilitydaily[22].toFixed(2)+"km";
            document.getElementById("visibility23").innerHTML = visibilitydaily[23].toFixed(2)+"km";
        }
        
        if(degree == "Farenheit")
        {
            document.getElementById("pressure0").innerHTML = pressure[0]+"mb";
            document.getElementById("pressure1").innerHTML = pressure[1]+"mb";
            document.getElementById("pressure2").innerHTML = pressure[2]+"mb";
            document.getElementById("pressure3").innerHTML = pressure[3]+"mb";
            document.getElementById("pressure4").innerHTML = pressure[4]+"mb";
            document.getElementById("pressure5").innerHTML = pressure[5]+"mb";
            document.getElementById("pressure6").innerHTML = pressure[6]+"mb";
            document.getElementById("pressure7").innerHTML = pressure[7]+"mb";
            document.getElementById("pressure8").innerHTML = pressure[8]+"mb";
            document.getElementById("pressure9").innerHTML = pressure[9]+"mb";
            document.getElementById("pressure10").innerHTML = pressure[10]+"mb";
            document.getElementById("pressure11").innerHTML = pressure[11]+"mb";
            document.getElementById("pressure12").innerHTML = pressure[12]+"mb";
            document.getElementById("pressure13").innerHTML = pressure[13]+"mb";
            document.getElementById("pressure14").innerHTML = pressure[14]+"mb";
            document.getElementById("pressure15").innerHTML = pressure[15]+"mb";
            document.getElementById("pressure16").innerHTML = pressure[16]+"mb";
            document.getElementById("pressure17").innerHTML = pressure[17]+"mb";
            document.getElementById("pressure18").innerHTML = pressure[18]+"mb";
            document.getElementById("pressure19").innerHTML = pressure[19]+"mb";
            document.getElementById("pressure20").innerHTML = pressure[20]+"mb";
            document.getElementById("pressure21").innerHTML = pressure[21]+"mb";
            document.getElementById("pressure22").innerHTML = pressure[22]+"mb";
            document.getElementById("pressure23").innerHTML = pressure[23]+"mb";
        }
        
        else
         {
            document.getElementById("pressure0").innerHTML = pressure[0]+"hPa";
            document.getElementById("pressure1").innerHTML = pressure[1]+"hPa";
            document.getElementById("pressure2").innerHTML = pressure[2]+"hPa";
            document.getElementById("pressure3").innerHTML = pressure[3]+"hPa";
            document.getElementById("pressure4").innerHTML = pressure[4]+"hPa";
            document.getElementById("pressure5").innerHTML = pressure[5]+"hPa";
            document.getElementById("pressure6").innerHTML = pressure[6]+"hPa";
            document.getElementById("pressure7").innerHTML = pressure[7]+"hPa";
            document.getElementById("pressure8").innerHTML = pressure[8]+"hPa";
            document.getElementById("pressure9").innerHTML = pressure[9]+"hPa";
            document.getElementById("pressure10").innerHTML = pressure[10]+"hPa";
            document.getElementById("pressure11").innerHTML = pressure[11]+"hPa";
            document.getElementById("pressure12").innerHTML = pressure[12]+"hPa";
            document.getElementById("pressure13").innerHTML = pressure[13]+"hPa";
            document.getElementById("pressure14").innerHTML = pressure[14]+"hPa";
            document.getElementById("pressure15").innerHTML = pressure[15]+"hPa";
            document.getElementById("pressure16").innerHTML = pressure[16]+"hPa";
            document.getElementById("pressure17").innerHTML = pressure[17]+"hPa";
            document.getElementById("pressure18").innerHTML = pressure[18]+"hPa";
            document.getElementById("pressure19").innerHTML = pressure[19]+"hPa";
            document.getElementById("pressure20").innerHTML = pressure[20]+"hPa";
            document.getElementById("pressure21").innerHTML = pressure[21]+"hPa";
            document.getElementById("pressure22").innerHTML = pressure[22]+"hPa";
            document.getElementById("pressure23").innerHTML = pressure[23]+"hPa";
        } 
                
          //Header values
        if(degree == "Farenheit")
        {
        document.getElementById("headertemperature").innerHTML = "Temp(&deg;F)";
        }
        else
        {
        document.getElementById("headertemperature").innerHTML = "Temp(&deg;C)";  
        }
        /*Tab2 values end*/
         /*Modal window screen values*/ 
        var modaltext1, modaltext2, modaltext3, modaltext4, modaltext5, modaltext6, modaltext7;
        modaltext1=" ";
        modaltext1+= "Weather in"+" "+cityname+ " "+MonthModalConverted[1]+" "+DateModal[1];
        modaltext2=" ";
        modaltext2+= "Weather in"+" "+cityname+" "+MonthModalConverted[2]+" "+DateModal[2];
        modaltext3=" ";
        modaltext3+="Weather in"+" "+cityname+" "+MonthModalConverted[3]+" "+DateModal[3];
        modaltext4=" ";
        modaltext4+= "Weather in"+" "+cityname+" "+MonthModalConverted[4]+" "+DateModal[4];
        modaltext5=" ";
        modaltext5+= "Weather in"+" "+cityname+" "+MonthModalConverted[5]+" "+DateModal[5];
        modaltext6=" ";
        modaltext6+= "Weather in"+" "+cityname+" "+MonthModalConverted[6]+" "+DateModal[6];
        modaltext7=" ";
        modaltext7+= "Weather in"+" "+cityname+" "+MonthModalConverted[7]+" "+DateModal[7];        
        
        document.getElementById("myModalLabel0").innerHTML = modaltext1;
        document.getElementById("modalimage0").innerHTML = imageweeklymodal[1];
        document.getElementById("summarymodal0").innerHTML= "<span style = "+"color:black"+">"+Daymodal[1]+":"+"</span>"+summarydailymodal[1];
        document.getElementById("sunriseTimemodal0").innerHTML = sunriseTimemodalConverted[1]+"AM";
        document.getElementById("sunsetTimemodal0").innerHTML = sunsetTimemodalConverted[1]+"PM";
        document.getElementById("humiditymodal0").innerHTML = (humiditymodal[1]*100).toFixed(0)+"%";
        if(degree == "Farenheit")
        {
            document.getElementById("windSpeedmodal0").innerHTML = (windSpeedmodal[1]).toFixed(2)+"mph";
            document.getElementById("visibilitymodal0").innerHTML = (visibilitymodal[1]).toFixed(2)+"mi";
            document.getElementById("pressuremodal0").innerHTML = pressuremodal[1]+"mb";
        }
        else
        {
            document.getElementById("windSpeedmodal0").innerHTML = (windSpeedmodal[1]).toFixed(2)+"m/s";
            document.getElementById("visibilitymodal0").innerHTML = (visibilitymodal[1]).toFixed(2)+"km";
            document.getElementById("pressuremodal0").innerHTML = pressuremodal[1]+"hPa";
        }
        
        
        document.getElementById("myModalLabel1").innerHTML = modaltext2;         
        document.getElementById("modalimage1").innerHTML = imageweeklymodal[2];
        document.getElementById("summarymodal1").innerHTML="<span style = "+"color:black"+">"+Daymodal[2]+":"+"</span>"+summarydailymodal[2];
        document.getElementById("sunriseTimemodal1").innerHTML = sunriseTimemodalConverted[2]+"AM";
        document.getElementById("sunsetTimemodal1").innerHTML = sunsetTimemodalConverted[2]+"PM";
        document.getElementById("humiditymodal1").innerHTML = (humiditymodal[2]*100).toFixed(0)+"%";
        if(degree == "Farenheit")
        {
        document.getElementById("windSpeedmodal1").innerHTML = (windSpeedmodal[2]).toFixed(2)+"mph";
        document.getElementById("visibilitymodal1").innerHTML = (visibilitymodal[2]).toFixed(2)+"mi";
        document.getElementById("pressuremodal1").innerHTML = (pressuremodal[2])+"mb";
        }
        else
        {
        document.getElementById("windSpeedmodal1").innerHTML = (windSpeedmodal[2]).toFixed(2)+"m/s";
        document.getElementById("visibilitymodal1").innerHTML = (visibilitymodal[2]).toFixed(2)+"km";
        document.getElementById("pressuremodal1").innerHTML = (pressuremodal[2])+"hPa";
        }
         
        document.getElementById("myModalLabel2").innerHTML = modaltext3;
        document.getElementById("modalimage2").innerHTML = imageweeklymodal[3];
        document.getElementById("summarymodal2").innerHTML= "<span style = "+"color:black"+">"+Daymodal[3]+":"+"</span>"+summarydailymodal[3];
        document.getElementById("sunriseTimemodal2").innerHTML = sunriseTimemodalConverted[3]+"AM";
        document.getElementById("sunsetTimemodal2").innerHTML = sunsetTimemodalConverted[3]+"PM";
        document.getElementById("humiditymodal2").innerHTML = (humiditymodal[3]*100).toFixed(0)+"%";
        if(degree == "Farenheit")
        {
        document.getElementById("windSpeedmodal2").innerHTML = (windSpeedmodal[3]).toFixed(2)+"mph";
        document.getElementById("visibilitymodal2").innerHTML = (visibilitymodal[3]).toFixed(2)+"mi";
        document.getElementById("pressuremodal2").innerHTML = (pressuremodal[3])+"mb";
        }
        else
        {
        document.getElementById("windSpeedmodal2").innerHTML = (windSpeedmodal[3]).toFixed(2)+"m/s";
        document.getElementById("visibilitymodal2").innerHTML = (visibilitymodal[3]).toFixed(2)+"km";
        document.getElementById("pressuremodal2").innerHTML = (pressuremodal[3])+"hPa";
        }
        
        document.getElementById("myModalLabel3").innerHTML = modaltext4;
        document.getElementById("modalimage3").innerHTML = imageweeklymodal[4];
        document.getElementById("summarymodal3").innerHTML= "<span style = "+"color:black"+">"+Daymodal[4]+":"+"</span>"+summarydailymodal[4];
        document.getElementById("sunriseTimemodal3").innerHTML = sunriseTimemodalConverted[4]+"AM";
        document.getElementById("sunsetTimemodal3").innerHTML = sunsetTimemodalConverted[4]+"PM";
        document.getElementById("humiditymodal3").innerHTML = (humiditymodal[4]*100).toFixed(0)+"%";
        if(degree == "Farenheit")
        {
        document.getElementById("windSpeedmodal3").innerHTML = (windSpeedmodal[4])+"mph";
        if(visibilitymodal[4] == undefined)
        {
        document.getElementById("visibilitymodal3").innerHTML = "NA";
        }
        document.getElementById("pressuremodal3").innerHTML = (pressuremodal[4])+"mb";
        }
        else
        {
        document.getElementById("windSpeedmodal3").innerHTML = (windSpeedmodal[4])+"m/s";
        if(visibilitymodal[4] == undefined)
        {
        document.getElementById("visibilitymodal3").innerHTML = "NA";
        }
        document.getElementById("pressuremodal3").innerHTML = (pressuremodal[4])+"hPa";
        }
      
                
        document.getElementById("myModalLabel4").innerHTML = modaltext5;
        document.getElementById("modalimage4").innerHTML = imageweeklymodal[5];
        document.getElementById("summarymodal4").innerHTML= "<span style = "+"color:black"+">"+Daymodal[5]+":"+"</span>"+summarydailymodal[5];
        document.getElementById("sunriseTimemodal4").innerHTML = sunriseTimemodalConverted[5]+"AM";
        document.getElementById("sunsetTimemodal4").innerHTML = sunsetTimemodalConverted[5]+"PM";
        document.getElementById("humiditymodal4").innerHTML = (humiditymodal[5]*100).toFixed(0)+"%";
        if(degree == "Farenheit")
        {
        document.getElementById("windSpeedmodal4").innerHTML = (windSpeedmodal[5])+"mph";
        if(visibilitymodal[5] == undefined)
        {
        document.getElementById("visibilitymodal4").innerHTML = "NA";
        }
        document.getElementById("pressuremodal4").innerHTML = (pressuremodal[5])+"mb";
        }
        else
        {
        document.getElementById("windSpeedmodal4").innerHTML = (windSpeedmodal[5])+"m/s";
        if(visibilitymodal[5] == undefined)
        {
        document.getElementById("visibilitymodal4").innerHTML = "NA";
        }
        document.getElementById("pressuremodal4").innerHTML = (pressuremodal[5])+"hPa";
        }
       
        
        document.getElementById("myModalLabel5").innerHTML = modaltext6;
        document.getElementById("modalimage5").innerHTML = imageweeklymodal[6];
        document.getElementById("summarymodal5").innerHTML= "<span style = "+"color:black"+">"+Daymodal[1]+":"+"</span>"+summarydailymodal[6];
        document.getElementById("sunriseTimemodal5").innerHTML = sunriseTimemodalConverted[6]+"AM";
        document.getElementById("sunsetTimemodal5").innerHTML = sunsetTimemodalConverted[6]+"PM";
        document.getElementById("humiditymodal5").innerHTML = (humiditymodal[6]*100).toFixed(0)+"%";
        if(degree == "Farenheit")
        {
        document.getElementById("windSpeedmodal5").innerHTML = (windSpeedmodal[6])+"mph";
        if(visibilitymodal[6] == undefined)
        {
        document.getElementById("visibilitymodal5").innerHTML = "NA";
        }
        document.getElementById("pressuremodal5").innerHTML = (pressuremodal[6])+"mb";
        }
        else
        {
         document.getElementById("windSpeedmodal5").innerHTML = (windSpeedmodal[6])+"m/s";
         if(visibilitymodal[6] == undefined)
        {
        document.getElementById("visibilitymodal5").innerHTML = "NA";
        }
        document.getElementById("pressuremodal5").innerHTML = (pressuremodal[6])+"hPa";
        }
       
        document.getElementById("myModalLabel6").innerHTML = modaltext7;
        document.getElementById("modalimage6").innerHTML = imageweeklymodal[7];
        document.getElementById("summarymodal6").innerHTML= "<span style = "+"color:black"+">"+Daymodal[1]+":"+"</span>"+summarydailymodal[7];
        document.getElementById("sunriseTimemodal6").innerHTML = sunriseTimemodalConverted[7]+"AM";
        document.getElementById("sunsetTimemodal6").innerHTML = sunsetTimemodalConverted[7]+"PM";
        document.getElementById("humiditymodal6").innerHTML = (humiditymodal[7]*100).toFixed(0)+"%";
        if(degree == "Farenheit")
        {
        document.getElementById("windSpeedmodal6").innerHTML = (windSpeedmodal[7])+"mph";
        if(visibilitymodal[7] == undefined)
        {
        document.getElementById("visibilitymodal6").innerHTML = "NA";
        }
        document.getElementById("pressuremodal6").innerHTML =(pressuremodal[7])+"mb";
        }
        else
        {
        document.getElementById("windSpeedmodal6").innerHTML = (windSpeedmodal[7])+"m/s";
        if(visibilitymodal[7] == undefined)
        {
        document.getElementById("visibilitymodal6").innerHTML = "NA";
        }
        document.getElementById("pressuremodal6").innerHTML =(pressuremodal[7])+"hPa"; 
        }
        
       
                
        /*Modal window screen values*/
                
            //document.getElementById("sunrisetime").innerHTML = rise;
        document.getElementById("image0").innerHTML = imageweekly[1];
        document.getElementById("image1").innerHTML = imageweekly[2];
        document.getElementById("image2").innerHTML = imageweekly[3];
        document.getElementById("image3").innerHTML = imageweekly[4];
        document.getElementById("image4").innerHTML = imageweekly[5];
        document.getElementById("image5").innerHTML = imageweekly[6];
        document.getElementById("image6").innerHTML = imageweekly[7];
        
        document.getElementById("temperatureMin0").innerHTML = Math.round(tempMinWeekly[1])+"&deg";
        document.getElementById("temperatureMax0").innerHTML = Math.round(tempMaxWeekly[1])+"&deg";
        document.getElementById("temperatureMin1").innerHTML = Math.round(tempMinWeekly[2])+"&deg";
        document.getElementById("temperatureMax1").innerHTML = Math.round(tempMaxWeekly[2])+"&deg";
        document.getElementById("temperatureMin2").innerHTML = Math.round(tempMinWeekly[3])+"&deg";
        document.getElementById("temperatureMax2").innerHTML = Math.round(tempMaxWeekly[3])+"&deg";
        document.getElementById("temperatureMin3").innerHTML = Math.round(tempMinWeekly[4])+"&deg";
        document.getElementById("temperatureMax3").innerHTML = Math.round(tempMaxWeekly[4])+"&deg";
        document.getElementById("temperatureMin4").innerHTML = Math.round(tempMinWeekly[5])+"&deg";
        document.getElementById("temperatureMax4").innerHTML = Math.round(tempMaxWeekly[5])+"&deg";
        document.getElementById("temperatureMin5").innerHTML = Math.round(tempMinWeekly[6])+"&deg";
        document.getElementById("temperatureMax5").innerHTML = Math.round(tempMaxWeekly[6])+"&deg";
        document.getElementById("temperatureMin6").innerHTML = Math.round(tempMinWeekly[7])+"&deg";
        document.getElementById("temperatureMax6").innerHTML = Math.round(tempMaxWeekly[7])+"&deg";
                
        document.getElementById("monthmodal0").innerHTML = MonthModalConverted[1]+" "+DateModal[1];
        document.getElementById("monthmodal1").innerHTML = MonthModalConverted[2]+" "+DateModal[2];
        document.getElementById("monthmodal2").innerHTML = MonthModalConverted[3]+" "+DateModal[3];
        document.getElementById("monthmodal3").innerHTML = MonthModalConverted[4]+" "+DateModal[4];
        document.getElementById("monthmodal4").innerHTML = MonthModalConverted[5]+" "+DateModal[5];
        document.getElementById("monthmodal5").innerHTML = MonthModalConverted[6]+" "+DateModal[6];
        document.getElementById("monthmodal6").innerHTML = MonthModalConverted[7]+" "+DateModal[7];
                
        document.getElementById("daymodal0").innerHTML = Daymodal[1];
        document.getElementById("daymodal1").innerHTML = Daymodal[2];
        document.getElementById("daymodal2").innerHTML = Daymodal[3];
        document.getElementById("daymodal3").innerHTML = Daymodal[4];
        document.getElementById("daymodal4").innerHTML = Daymodal[5];
        document.getElementById("daymodal5").innerHTML = Daymodal[6];
        document.getElementById("daymodal6").innerHTML = Daymodal[7];
        
        
            
        }
});
return false;
});
});
</script>
</head>
  <body>
<script>
          window.fbAsyncInit = function() {
            FB.init({
              appId      : '505445369632898',
              xfbml      : true,
              version    : 'v2.5'
            });
          };

          (function(d, s, id){
             var js, fjs = d.getElementsByTagName(s)[0];
             if (d.getElementById(id)) {return;}
             js = d.createElement(s); js.id = id;
             js.src = "//connect.facebook.net/en_US/sdk.js";
             fjs.parentNode.insertBefore(js, fjs);
           }(document, 'script', 'facebook-jssdk'));
    
function statusChangeCallback(response) {
    console.log('statusChangeCallback');
    console.log(response);
//alert(response);
    if (response.status === 'connected') {
      testAPI();
    }
else if (response.status === 'not_authorized') {
      alert("Not Posted");
    } else {
      alert("Not Posted");
    }
  }
        
function testAPI() {
    
FB.ui({
  method: 'feed',
  name: 'Current Weather in '+cityname+','+stateName,
  link: 'http://forecast.io/',
  picture:"http://cs-server.usc.edu:45678/hw/hw8/images/"+icon_image,
  description: summary,
caption: 'WEATHER INFORMATION FROM FORECAST.IO',
}, function(response){
if(response==null)
alert('Not Posted');
else
alert('Posted Successfully');
});
}
        
function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
  }
</script>
    <h1 align = "center">Forecast search</h1>
    <div id="outer-div" style="margin-top:40px;" class="container-fluid">
     <div class = "well">
    <form class ="form-inline" role="form" id = "QueryForm" method="get">
          <div class = "col-md-1"></div>
          <div class = "container">
          <div class="form-group">
            <label for="StreetAddress" style="color:white;">StreetAddress:<span style="color: red;">*</span></label><br>
            <input type ="text" id="streetaddress" name="streetAdd" class="form-control" placeholder="Enter Street Address"><br>
            <span id="error1"></span>
          </div>
        
          <div class="form-group">
            <label for="city" style="color:white;">City:<span style="color: red;">*</span></label><br>
            <input type ="text" class="form-control" id="cityname" name = "cityname" placeholder="Enter city name"><br>
            <span id="error2"></span>
          </div>
    
          <div class="form-group">
            <label for="StateName" style="color:white;">State:<span style="color: red;">*</span></label><br>
           <select class="form-control" id = "StateName" name = "StateName">
            <option value="SelectName">Select your state</option>
                <option value="AL">Alabama</option>
                <option value="AK">Alaska</option>
                <option value="AZ">Arizona</option>
                <option value="AR">Arkansas</option>
                <option value="CA">California</option>
                <option value="CO">Colorado</option>
                <option value="CT">Connecticut</option>
                <option value="DE">Delaware</option>
                <option value="DC">District of Columbia</option>
                <option value="FL">Florida</option>
                <option value="GA">Georgia</option>
                <option value="HI">Hawaii</option>
                <option value="ID">Idaho</option>
                <option value="IL">Illinois</option>
                <option value="IN">Indiana</option>
                <option value="IA">Iowa</option>
                <option value="KS">Kansas</option>
                <option value="KY">Kentucky</option>
                <option value="LA">Louisiana</option>
                <option value="ME">Maine</option>
                <option value="MD">Maryland</option>
                <option value="MA">Massachusetts</option>
                <option value="MI">Michigan</option>
                <option value="MN">Minnesota</option>
                <option value="MS">Mississippi</option>
                <option value="MO">Missouri</option>
                <option value="MT">Montana</option>
                <option value="NE">Nebraska</option>
                <option value="NV">Nevada</option>
                <option value="NH">New Hampshire</option>
                <option value="NJ">New Jersey</option>
                <option value="NM">New Mexico</option>
                <option value="NY">New York</option>
                <option value="NC">North Carolina</option>
                <option value="ND">North Dakota</option>
                <option value="OH">Ohio</option>
                <option value="OK">Oklahoma</option>
                <option value="OR">Oregon</option>
                <option value="PA">Pennsylvania</option>
                <option value="RI">Rhode Island</option>
                <option value="SC">South Carolina</option>
                <option value="SD">South Dakota</option>
                <option value="TN">Tennessee</option>
                <option value="TX">Texas</option>
                <option value="UT">Utah</option>
                <option value="VT">Vermont</option>
                <option value="VA">Virginia</option>
                <option value="WA">Washington</option>
                <option value="WV">West Virginia</option>
                <option value="WI">Wisconsin</option>
                <option value="WY">Wyoming</option>
            </select><br>
            <span id = "error3"></span>
            </div>
            <div class="form-group">
            <label for ="FCData" class = "control-label" style="color:white;">Degree:<span style = "color:red;">*</span></label><br>
                <label class="radio-inline" style="color:white;">
                  <input name="radioGroup" id="radio1" value="Farenheit" type="radio" checked="checked">Farenheit
                </label>
                <label class="radio-inline" style="color:white;">
                  <input name="radioGroup" id="radio2" value="Celcius" type="radio">Celcius
                </label>
            </div>
            <div class ="form-group" align = "right"><br>
                <button type="submit" class="btn btn-default" name = "Search">
                   Search <span class="glyphicon glyphicon-search"></span></button> 
                 <button type="reset" class="btn" name ="Clear" onclick="ResetContents()">Clear
                  <span class="glyphicon glyphicon-refresh"></span> 
                </button>
            </div>
            
          </div>
        
            <div class = "container" id = "secondLine" align = "right">
                <div class = "col-md-6"></div>
                <div class = "col-md-4">
                <label for = "poweredby" class = "control-label" style="color:white;">Powered By:</label>
                <a href = "http://forecast.io"><img src = "http://cs-server.usc.edu:45678/hw/hw8/images/forecast_logo.png" height="40px" width="90px"></a>
            </div>
            </div>
            </form>
            </div> 
            <hr style="width:auto; color: white; border-width:2px; display:block;" />
      </div>
    
    <!--The tab display-->
    <div class="container" id = "parseddata">
    <ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="#displaydata">Right Now</a></li>
    <li><a data-toggle="pill" href="#Next24hours">Next 24 hours</a></li>
    <li><a data-toggle="pill" href="#Next7days">Next 7 Days</a></li>
    </ul>
    
    <!--The tab content-->
     <div class="tab-content">
        <div id="displaydata" class="tab-pane fade in active">
          <div class = "container">
              <div class="row">
                  <div class = "col-md-5">
                            <div class = "row" style="background-color:rgb(225,102,102);">
                                 <div class = "col-md-5" align ="center" style="padding-top:10px; height:100px;">
                                     <div id ="imageicon"></div>
                                 </div>
                                 <div class = "col-md-5" style="height:100px;">
                                                 <div id ="clubbedheaderdata"></div>
                                             </div>
                                             <div class ="col-md-5">
                                            <div style="position:relative;width:27px;height:27px;" >
                                            <div style="position:absolute;left:430px;top:-10px;width:27px;height:27px;z-index:111;">
                                             <img src="http://cs-server.usc.edu:37430/Homework8/fb_icon-Copy.png" class="img-responsive" style="cursor:pointer;display:inline-block;" />
                                             </div>
                                           <div style="position:absolute;left:430px;top:-10px;width:27px;height:27px;overflow:hidden;z-index:333;opacity:0;filter:alpha(opacity=0);">
                                            <fb:login-button width="27" height="27" scope="public_profile,email" onlogin="checkLoginState();">
                                            </fb:login-button>
                                            </div>
                                            </div>
                                             </div>
                                
                          
                          <table class="table table-striped" id = "displaydatatable">
                          <tbody>
                              <tr>
                              <td>Precipitation</td>
                              <td id ="precipitationvalue"></td>
                              </tr>
                              <tr>
                               <td>Chance of Rain</td>
                               <td id ="chancerain"></td>
                              </tr>
                              <tr>
                                  <td>Wind Speed</td>
                                  <td id = "wind"></td>
                              </tr>
                              <tr>
                                <td>Dew Point</td>
                                <td id = "dewpoint"></td>
                              </tr>
                              <tr>
                                <td>Humidity</td>
                                <td id ="humidity"></td>
                              </tr>
                              <tr>
                                <td>Visibility</td>
                                <td id = "visibility"></td>
                              </tr>
                              <tr>
                                  <td>Sunrise Time</td>
                                  <td id = "sunrisetime"></td>
                              </tr>
                              <tr>
                                  <td>Sunset Time</td>
                                  <td id = "sunsettime"></td>
                              </tr>
                          </tbody>
                      </table>
                     </div>
                  </div>
                  <div class="col-md-7">
                        <div id = "right" class="well">
                        </div>
                    </div>
              </div>
          </div>
        </div>
        <div id="Next24hours" class="tab-pane fade">
          <div class = "container-fluid">
              <div class = "row">
                  <div class = "col-xs-12">
                      <table class = "table" id = "Next24hourstable">
                          <thead id = "Next24hrshead">
                          <tr>
                            <th style="text-align:center;">Time</th>
                            <th style="text-align:center;">Summary</th>
                            <th style="text-align:center;">Cloud Cover</th>
                            <th style="text-align:center;" id= "headertemperature"></th>
                            <th style="text-align:center;">View Details</th>
                          </tr>
                          </thead>
                          <tbody id ="Next24hrsbody">
                              <tr>
                                  <td id = "timedaily0" style="text-align:center;"></td>
                                  <td id = "Summary0" style="text-align:center;"></td>
                                  <td id = "cloudcover0" style="text-align:center;"></td>
                                  <td id = "temperature0" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme0" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                               <tr rowspan = "3" class = "collapse" id = "collapseme0" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;"  id = "windspeed0"></td>
                                         <td style = "text-align:center;" id = "humidity0"></td>
                                         <td style = "text-align:center;" id = "visibility0"></td>
                                         <td style = "text-align:center;"  id = "pressure0"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily1" style="text-align:center;"></td>
                                  <td id = "Summary1" style="text-align:center;"></td>
                                  <td id = "cloudcover1" style="text-align:center;"></td>
                                  <td id = "temperature1" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme1" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme1" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;"  id = "windspeed1"></td>
                                         <td style = "text-align:center;"  id = "humidity1"></td>
                                         <td style = "text-align:center;" id = "visibility1"></td>
                                         <td style = "text-align:center;" id = "pressure1"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              
                              <tr>
                                  <td id = "timedaily2" style="text-align:center;"></td>
                                  <td id = "Summary2" style="text-align:center;"></td>
                                  <td id = "cloudcover2" style="text-align:center;"></td>
                                  <td id = "temperature2" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme2" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme2" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed2"></td>
                                         <td style = "text-align:center;" id = "humidity2"></td>
                                         <td style = "text-align:center;" id = "visibility2"></td>
                                         <td style = "text-align:center;" id = "pressure2"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily3" style="text-align:center;"></td>
                                  <td id = "Summary3" style="text-align:center;"></td>
                                  <td id = "cloudcover3" style="text-align:center;"></td>
                                  <td id = "temperature3" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme3" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme3" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed3"></td>
                                         <td style = "text-align:center;" id = "humidity3"></td>
                                         <td style = "text-align:center;" id = "visibility3"></td>
                                         <td style = "text-align:center;" id = "pressure3"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily4" style="text-align:center;"></td>
                                  <td id = "Summary4" style="text-align:center;"></td>
                                  <td id = "cloudcover4" style="text-align:center;"></td>
                                  <td id = "temperature4" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme4" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme4" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed4"></td>
                                         <td style = "text-align:center;" id = "humidity4"></td>
                                         <td style = "text-align:center;" id = "visibility4"></td>
                                         <td style = "text-align:center;" id = "pressure4"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily5" style="text-align:center;"></td>
                                  <td id = "Summary5" style="text-align:center;"></td>
                                  <td id = "cloudcover5" style="text-align:center;"></td>
                                  <td id = "temperature5" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme5" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme5" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed5"></td>
                                         <td style = "text-align:center;" id = "humidity5"></td>
                                         <td style = "text-align:center;" id = "visibility5"></td>
                                         <td style = "text-align:center;" id = "pressure5"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily6" style="text-align:center;"></td>
                                  <td id = "Summary6" style="text-align:center;"></td>
                                  <td id = "cloudcover6" style="text-align:center;"></td>
                                  <td id = "temperature6" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme6" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme6" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed6"></td>
                                         <td style = "text-align:center;" id = "humidity6"></td>
                                         <td style = "text-align:center;" id = "visibility6"></td>
                                         <td style = "text-align:center;" id = "pressure6"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily7" style="text-align:center;"></td>
                                  <td id = "Summary7" style="text-align:center;"></td>
                                  <td id = "cloudcover7" style="text-align:center;"></td>
                                  <td id = "temperature7" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme7" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme7" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed7"></td>
                                         <td style = "text-align:center;" id = "humidity7"></td>
                                         <td style = "text-align:center;" id = "visibility7"></td>
                                         <td style = "text-align:center;" id = "pressure7"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily8" style="text-align:center;"></td>
                                  <td id = "Summary8" style="text-align:center;"></td>
                                  <td id = "cloudcover8" style="text-align:center;"></td>
                                  <td id = "temperature8" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme8" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme8" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed8"></td>
                                         <td style = "text-align:center;" id = "humidity8"></td>
                                         <td style = "text-align:center;" id = "visibility8"></td>
                                         <td style = "text-align:center;" id = "pressure8"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily9" style="text-align:center;"></td>
                                  <td id = "Summary9" style="text-align:center;"></td>
                                  <td id = "cloudcover9" style="text-align:center;"></td>
                                  <td id = "temperature9" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme9" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme9" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed9"></td>
                                         <td style = "text-align:center;" id = "humidity9"></td>
                                         <td style = "text-align:center;" id = "visibility9"></td>
                                         <td style = "text-align:center;" id = "pressure9"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily10" style="text-align:center;"></td>
                                  <td id = "Summary10" style="text-align:center;"></td>
                                  <td id = "cloudcover10" style="text-align:center;"></td>
                                  <td id = "temperature10" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme10" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme10" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed10"></td>
                                         <td style = "text-align:center;" id = "humidity10"></td>
                                         <td style = "text-align:center;" id = "visibility10"></td>
                                         <td style = "text-align:center;" id = "pressure10"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily11" style="text-align:center;"></td>
                                  <td id = "Summary11" style="text-align:center;"></td>
                                  <td id = "cloudcover11" style="text-align:center;"></td>
                                  <td id = "temperature11" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme11" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme11" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed11"></td>
                                         <td style = "text-align:center;" id = "humidity11"></td>
                                         <td style = "text-align:center;" id = "visibility11"></td>
                                         <td style = "text-align:center;" id = "pressure11"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily12" style="text-align:center;"></td>
                                  <td id = "Summary12" style="text-align:center;"></td>
                                  <td id = "cloudcover12" style="text-align:center;"></td>
                                  <td id = "temperature12" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme12" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme12" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed12"></td>
                                         <td style = "text-align:center;" id = "humidity12"></td>
                                         <td style = "text-align:center;" id = "visibility12"></td>
                                         <td style = "text-align:center;" id = "pressure12"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily13" style="text-align:center;"></td>
                                  <td id = "Summary13" style="text-align:center;"></td>
                                  <td id = "cloudcover13" style="text-align:center;"></td>
                                  <td id = "temperature13" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme13" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme13" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed13"></td>
                                         <td style = "text-align:center;" id = "humidity13"></td>
                                         <td style = "text-align:center;" id = "visibility13"></td>
                                         <td style = "text-align:center;" id = "pressure13"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily14" style="text-align:center;"></td>
                                  <td id = "Summary14" style="text-align:center;"></td>
                                  <td id = "cloudcover14" style="text-align:center;"></td>
                                  <td id = "temperature14" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme14" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme14" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed14"></td>
                                         <td style = "text-align:center;" id = "humidity14"></td>
                                         <td style = "text-align:center;" id = "visibility14"></td>
                                         <td style = "text-align:center;" id = "pressure14"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily15" style="text-align:center;"></td>
                                  <td id = "Summary15" style="text-align:center;"></td>
                                  <td id = "cloudcover15" style="text-align:center;"></td>
                                  <td id = "temperature15" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme15" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme15" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed15"></td>
                                         <td style = "text-align:center;" id = "humidity15"></td>
                                         <td style = "text-align:center;" id = "visibility15"></td>
                                         <td style = "text-align:center;" id = "pressure15"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily16" style="text-align:center;"></td>
                                  <td id = "Summary16" style="text-align:center;"></td>
                                  <td id = "cloudcover16" style="text-align:center;"></td>
                                  <td id = "temperature16" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme16" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme16" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed16"></td>
                                         <td style = "text-align:center;" id = "humidity16"></td>
                                         <td style = "text-align:center;" id = "visibility16"></td>
                                         <td style = "text-align:center;" id = "pressure16"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily17" style="text-align:center;"></td>
                                  <td id = "Summary17" style="text-align:center;"></td>
                                  <td id = "cloudcover17" style="text-align:center;"></td>
                                  <td id = "temperature17" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme17" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme17" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed17"></td>
                                         <td style = "text-align:center;" id = "humidity17"></td>
                                         <td style = "text-align:center;" id = "visibility17"></td>
                                         <td style = "text-align:center;" id = "pressure17"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily18" style="text-align:center;"></td>
                                  <td id = "Summary18" style="text-align:center;"></td>
                                  <td id = "cloudcover18" style="text-align:center;"></td>
                                  <td id = "temperature18" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme18" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme18" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed18"></td>
                                         <td style = "text-align:center;" id = "humidity18"></td>
                                         <td style = "text-align:center;" id = "visibility18"></td>
                                         <td style = "text-align:center;" id = "pressure18"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily19" style="text-align:center;"></td>
                                  <td id = "Summary19" style="text-align:center;"></td>
                                  <td id = "cloudcover19" style="text-align:center;"></td>
                                  <td id = "temperature19" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme19" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme19" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed19"></td>
                                         <td style = "text-align:center;" id = "humidity19"></td>
                                         <td style = "text-align:center;" id = "visibility19"></td>
                                         <td style = "text-align:center;" id = "pressure19"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily20" style="text-align:center;"></td>
                                  <td id = "Summary20" style="text-align:center;"></td>
                                  <td id = "cloudcover20" style="text-align:center;"></td>
                                  <td id = "temperature20" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme20" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme20" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                        <td style = "text-align:center;" id = "windspeed20"></td>
                                         <td style = "text-align:center;" id = "humidity20"></td>
                                         <td style = "text-align:center;" id = "visibility20"></td>
                                         <td style = "text-align:center;" id = "pressure20"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily21" style="text-align:center;"></td>
                                  <td id = "Summary21" style="text-align:center;"></td>
                                  <td id = "cloudcover21" style="text-align:center;"></td>
                                  <td id = "temperature21" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme21" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme21" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed21"></td>
                                         <td style = "text-align:center;" id = "humidity21"></td>
                                         <td style = "text-align:center;" id = "visibility21"></td>
                                         <td style = "text-align:center;" id = "pressure21"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                              <tr>
                                  <td id = "timedaily22" style="text-align:center;"></td>
                                  <td id = "Summary22" style="text-align:center;"></td>
                                  <td id = "cloudcover22" style="text-align:center;"></td>
                                  <td id = "temperature22" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme22" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme22" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed22"></td>
                                         <td style = "text-align:center;" id = "humidity22"></td>
                                         <td style = "text-align:center;" id = "visibility22"></td>
                                         <td style = "text-align:center;" id = "pressure22"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>

                              <tr>
                                  <td id = "timedaily23" style="text-align:center;"></td>
                                  <td id = "Summary23" style="text-align:center;"></td>
                                  <td id = "cloudcover23" style="text-align:center;"></td>
                                  <td id = "temperature23" style="text-align:center;"></td>
                                  <td style="text-align:center;"><a href="#collapseme23" data-toggle = "collapse"><span class="glyphicon glyphicon-plus"></span></a></td>
                              </tr>
                              <tr rowspan = "3" class = "collapse" id = "collapseme23" style="background-color:#F1F2F1">
                                  <td colspan = "5">
                                    <div>
                                       <table class = "table table-responsive">
                                         <tr>
                                          <td style = "text-align:center; font-weight:bold;">Wind</td>
                                          <td style = "text-align:center; font-weight:bold;">Humidity</td>
                                          <td style = "text-align:center; font-weight:bold;">Visibility</td>
                                          <td style = "text-align:center; font-weight:bold;">Pressure</td>
                                         </tr>
                                         <tr style="background-color:#F1F2F1">
                                         <td style = "text-align:center;" id = "windspeed23"></td>
                                         <td style = "text-align:center;" id = "humidity23"></td>
                                         <td style = "text-align:center;" id = "visibility23"></td>
                                         <td style = "text-align:center;" id = "pressure23"></td>
                                         </tr>
                                       </table>
                                    </div>
                                  </td>
                              </tr>
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
        </div>
         
         <!--Tab 3 starting-->
        <div id="Next7days" class="tab-pane fade col-sm-12" style="background-color:black; padding-top:10px;padding-bottom:10px;">
            <div class = "col-sm-2"></div>
            <div class="col-sm-1" style = "height:320px; background-color:#3366CC; text-align:center; border-radius: 5px; margin-left:10px;" data-toggle="modal" data-target="#myModal0">
                <p></p>
                <p style="color:white;" id ="daymodal0"></p>
                <p style="color:white;" id ="monthmodal0"></p>
                <p id = "image0"></p>
                <p style="color:white;">&nbsp;Min<br>Temp</p>
                <p id ="temperatureMin0" style="color:white; font-size:24px; font-weight:bold;"></p>
                <p style="color:white;">&nbsp;Max<br>Temp</p>
                <p id = "temperatureMax0" style="color:white; font-size:24px; font-weight:bold;"></p>
            </div>
            <div class="col-sm-1" style = "height:320px; background-color:#A40000; text-align:center; border-radius: 5px; margin-left:10px;"data-toggle="modal" data-target="#myModal1">
                <p></p>
                <p  style="color:white;" id ="daymodal1"></p>
                <p  style="color:white;" id ="monthmodal1"></p>
                <p id = "image1"></p>
                <p style="color:white;">&nbsp;Min<br>Temp</p>
                <p id ="temperatureMin1" style="color:white; font-size:24px; font-weight:bold;"></p>
                <p style="color:white;">&nbsp;Max<br>Temp</p>
                <p id = "temperatureMax1" style="color:white; font-size:24px; font-weight:bold;"></p>
            </div>
            
            <div class="col-sm-1" style = "height:320px; background-color:rgb(255,134,40); text-align:center; border-radius: 5px; margin-left:10px;" data-toggle="modal" data-target="#myModal2">
                <p></p>
                <p style="color:white;" id ="daymodal2"></p>
                <p style="color:white;" id ="monthmodal2"></p>
                <p id = "image2"></p>
                <p style="color:white;">&nbsp;Min<br>Temp</p>
                <p id ="temperatureMin2" style="color:white; font-size:24px; font-weight:bold;"></p>
                <p style="color:white;">&nbsp;Max<br>Temp</p>
                <p id = "temperatureMax2" style="color:white; font-size:24px; font-weight:bold;"></p>
            </div>
            
            <div class="col-sm-1" style = "height:320px; background-color:rgb(102,153,0); text-align:center; border-radius: 5px; margin-left:10px;" data-toggle="modal" data-target="#myModal3">
                <p></p>
                <p style="color:white;" id ="daymodal3"></p>
                <p style="color:white;" id ="monthmodal3"></p>
                <p id = "image3"></p>
                <p style="color:white;">&nbsp;Min<br>Temp</p>
                <p id ="temperatureMin3" style="color:white; font-size:24px; font-weight:bold;"></p>
                <p style="color:white;">&nbsp;Max<br>Temp</p>
                <p id = "temperatureMax3" style="color:white; font-size:24px; font-weight:bold;"></p>
            </div>
            
            <div class="col-sm-1" style = "height:320px; background-color:rgb(125,86,153); text-align:center; border-radius: 5px;margin-left:10px;" data-toggle="modal" data-target="#myModal4">
                <p></p>
                <p style="color:white;" id ="daymodal4"></p>
                <p style="color:white;" id ="monthmodal4"></p>
                <p id = "image4"></p>
                <p style="color:white;">&nbsp;Min<br>Temp</p>
                <p id ="temperatureMin4" style="color:white; font-size:24px; font-weight:bold;"></p>
                <p style="color:white;">&nbsp;Max<br>Temp</p>
                <p id = "temperatureMax4" style="color:white; font-size:24px; font-weight:bold;"></p>
            </div>
            
            <div class="col-sm-1" style = "height:320px; background-color:rgb(255,102,102); text-align:center; border-radius: 5px; margin-left:10px;" data-toggle="modal" data-target="#myModal5">
                <p></p>
                <p style="color:white;" id ="daymodal5"></p>
                <p style="color:white;" id ="monthmodal5"></p>
                <p id = "image5"></p>
                <p style="color:white;">&nbsp;Min<br>Temp</p>
                <p id ="temperatureMin5" style="color:white; font-size:24px; font-weight:bold;"></p>
                <p style="color:white;">&nbsp;Max<br>Temp</p>
                <p id = "temperatureMax5" style="color:white; font-size:24px; font-weight:bold;"></p>
            </div>
            
            <div class="col-sm-1" style = "height:320px; background-color:rgb(213,0,102); text-align:center; border-radius: 5px; margin-left:10px;" data-toggle="modal" data-target="#myModal6">
                <p></p>
                <p style="color:white;" id ="daymodal6"></p>
                <p  style="color:white;" id ="monthmodal6"></p>
                <p id = "image6"></p>
                <p style="color:white;">&nbsp;Min<br>Temp</p>
                <p id ="temperatureMin6" style="color:white; font-size:24px; font-weight:bold;"></p>
                <p style="color:white;">&nbsp;Max<br>Temp</p>
                <p id = "temperatureMax6" style="color:white; font-size:24px; font-weight:bold;"></p>
            </div>
        <!-- Modal window 1-->    
        <div class="modal fade" id="myModal0" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>                               </button>
                        <h4 class="modal-title" id="myModalLabel0"></h4>
                      </div>
                      <div class="modal-body" align = "center">
                          <div id ="modalimage0" style="text-align :center;"></div>
                          <div id ="summarymodal0" style = "font-size:30px; color:rgb(255,134,40); text-align :center;"></div>
                          <div class="row">
                              <div class = "col-md-1"></div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunrise Time</p>
                                  <p style="font-size:16px;" id ="sunriseTimemodal0"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Wind Speed</p>
                                  <p id = "windSpeedmodal0" style="font-size:16px;"></p>
                              </div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunset Time</p>
                                  <p style="font-size:16px;" id ="sunsetTimemodal0"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Visibility</p>
                                  <p id = "visibilitymodal0" style="font-size:16px;"></p>
                              </div>
                              <div>
                                  <p style="font-weight:bold; font-size: 20px;">Humidity</p>
                                  <p id = "humiditymodal0" style="font-size:16px;"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Pressure</p>
                                  <p id = "pressuremodal0" style="font-size:16px;"></p>
                              </div>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
            
            <!-- Modal window 2-->
            <div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>                               </button>
                        <h4 class="modal-title" id="myModalLabel1"></h4>
                      </div>
                      <div class="modal-body" align = "center">
                          <div id ="modalimage1" style="text-align :center;"></div>
                          <div id ="summarymodal1" style = "font-size:30px; color:rgb(255,134,40); text-align :center;"></div>
                          <div class="row">
                              <div class = "col-md-1"></div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunrise Time</p>
                                  <p style="font-size:16px;" id = "sunriseTimemodal1"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Wind Speed</p>
                                  <p id = "windSpeedmodal1" style="font-size:16px;"></p>
                              </div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunset Time</p>
                                  <p style="font-size:16px;" id = "sunsetTimemodal1"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Visibility</p>
                                  <p id = "visibilitymodal1" style="font-size:16px;"></p>
                              </div>
                              <div>
                                  <p style="font-weight:bold; font-size: 20px;">Humidity</p>
                                  <p id = "humiditymodal1" style="font-size:16px;"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Pressure</p>
                                  <p id = "pressuremodal1" style="font-size:16px;"></p>
                              </div>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
            
            <!-- Modal window 3-->
                 <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>                               </button>
                        <h4 class="modal-title" id="myModalLabel2"></h4>
                      </div>
                      <div class="modal-body" align = "center">
                          <div id ="modalimage2" style="text-align :center;"></div>
                          <div id ="summarymodal2" style = "font-size:30px; color:rgb(255,134,40); text-align :center;"></div>
                          <div class="row">
                              <div class = "col-md-1"></div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunrise Time</p>
                                  <p style="font-size:16px;" id = "sunriseTimemodal2"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Wind Speed</p>
                                  <p id = "windSpeedmodal2" style="font-size:16px;"></p>
                              </div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunset Time</p>
                                  <p style="font-size:16px;" id = "sunsetTimemodal2"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Visibility</p>
                                  <p id = "visibilitymodal2" style="font-size:16px;"></p>
                              </div>
                              <div>
                                  <p style="font-weight:bold; font-size: 20px;">Humidity</p>
                                  <p id = "humiditymodal2" style="font-size:16px;"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Pressure</p>
                                  <p id = "pressuremodal2" style="font-size:16px;"></p>
                              </div>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
            
            
            <!-- Modal window 4-->
                <div class="modal fade" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>                               </button>
                        <h4 class="modal-title" id="myModalLabel3"></h4>
                      </div>
                      <div class="modal-body" align = "center">
                          <div id ="modalimage3" style="text-align :center;"></div>
                          <div id ="summarymodal3" style = "font-size:30px; color:rgb(255,134,40); text-align :center;"></div>
                          <div class="row">
                              <div class = "col-md-1"></div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunrise Time</p>
                                  <p style="font-size:16px;" id ="sunriseTimemodal3"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Wind Speed</p>
                                  <p id = "windSpeedmodal3" style="font-size:16px;"></p>
                              </div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunset Time</p>
                                  <p style="font-size:16px;" id = "sunsetTimemodal3"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Visibility</p>
                                  <p id = "visibilitymodal3" style="font-size:16px;"></p>
                              </div>
                              <div>
                                  <p style="font-weight:bold; font-size: 20px;">Humidity</p>
                                  <p id = "humiditymodal3" style="font-size:16px;"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Pressure</p>
                                  <p id = "pressuremodal3" style="font-size:16px;"></p>
                              </div>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
            
            
            <!-- Modal window 5-->
                <div class="modal fade" id="myModal4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>                               </button>
                        <h4 class="modal-title" id="myModalLabel4"></h4>
                      </div>
                      <div class="modal-body" align = "center">
                          <div id ="modalimage4" style="text-align :center;"></div>
                          <div id ="summarymodal4" style = "font-size:30px; color:rgb(255,134,40); text-align :center;"></div>
                          <div class="row">
                              <div class = "col-md-1"></div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunrise Time</p>
                                  <p style="font-size:16px;" id = "sunriseTimemodal4"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Wind Speed</p>
                                  <p id = "windSpeedmodal4" style="font-size:16px;"></p>
                              </div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunset Time</p>
                                  <p style="font-size:16px;" id = "sunsetTimemodal4"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Visibility</p>
                                  <p id = "visibilitymodal4" style="font-size:16px;"></p>
                              </div>
                              <div>
                                  <p style="font-weight:bold; font-size: 20px;">Humidity</p>
                                  <p id = "humiditymodal4" style="font-size:16px;"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Pressure</p>
                                  <p id = "pressuremodal4" style="font-size:16px;"></p>
                              </div>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
             
            <!-- Modal window 6-->
                <div class="modal fade" id="myModal5" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>                               </button>
                        <h4 class="modal-title" id="myModalLabel5"></h4>
                      </div>
                      <div class="modal-body" align = "center">
                          <div id ="modalimage5" style="text-align :center;"></div>
                          <div id ="summarymodal5" style = "font-size:30px; color:rgb(255,134,40); text-align :center;"></div>
                          <div class="row">
                              <div class = "col-md-1"></div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunrise Time</p>
                                  <p style="font-size:16px;" id = "sunriseTimemodal5"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Wind Speed</p>
                                  <p id = "windSpeedmodal5" style="font-size:16px;"></p>
                              </div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunset Time</p>
                                  <p style="font-size:16px;" id ="sunsetTimemodal5"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Visibility</p>
                                  <p id = "visibilitymodal5" style="font-size:16px;"></p>
                              </div>
                              <div>
                                  <p style="font-weight:bold; font-size: 20px;">Humidity</p>
                                  <p id = "humiditymodal5" style="font-size:16px;"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Pressure</p>
                                  <p id = "pressuremodal5" style="font-size:16px;"></p>
                              </div>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
            
            <!-- Modal window 7-->
                <div class="modal fade" id="myModal6" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>                               </button>
                        <h4 class="modal-title" id="myModalLabel6"></h4>
                      </div>
                      <div class="modal-body" align = "center">
                          <div id ="modalimage6" style="text-align :center;"></div>
                          <div id ="summarymodal6" style = "font-size:30px; color:rgb(255,134,40); text-align :center;"></div>
                          <div class="row">
                              <div class = "col-md-1"></div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunrise Time</p>
                                  <p style="font-size:16px;" id = "sunriseTimemodal6"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Wind Speed</p>
                                  <p id = "windSpeedmodal6" style="font-size:16px;"></p>
                              </div>
                              <div class="col-md-4">
                                  <p style="font-weight:bold; font-size: 20px;">Sunset Time</p>
                                  <p style="font-size:16px;" id = "sunsetTimemodal6"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Visibility</p>
                                  <p id = "visibilitymodal6" style="font-size:16px;"></p>
                              </div>
                              <div>
                                  <p style="font-weight:bold; font-size: 20px;">Humidity</p>
                                  <p id = "humiditymodal6" style="font-size:16px;"></p>
                                  <p style="font-weight:bold; font-size: 20px;">Pressure</p>
                                  <p id = "pressuremodal6" style="font-size:16px;"></p>
                              </div>
                          </div>
                      </div>
                      <div class="modal-footer">
                       <button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
            
    </div>
  </body>