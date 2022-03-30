<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>TownBuddy</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <style>
            body, html {
              height: 100%;
              margin: 0;
            }
            
            .bgimg {
              background-image: url('/assets/bg1.jpg');
              height: 100%;
              background-position: center;
              background-size: cover;
              position: relative;
              color: white;
              font-family: "Courier New", Courier, monospace;
              font-size: 25px;
            }
            
            .topleft {
              position: absolute;
              top: 0;
              left: 16px;
            }
            
            .bottomleft {
              position: absolute;
              bottom: 0;
              left: 16px;
            }
            
            .middle {
              position: absolute;
              top: 50%;
              left: 50%;
              transform: translate(-50%, -50%);
              text-align: center;
            }
            
            hr {
              margin: auto;
              width: 40%;
            }
            .walking_john{
                position: fixed;
                z-index: 10000;
                width: 200px;
                height: 200px;
                bottom: 161px;
                left:-10px;
                animation: mymove 10s infinite;
                animation-timing-function: linear;
            }
            
            @keyframes mymove {
              from {left: -20%;}
              to {left: 100%;}
            }
            </style>
    </head>
    <body>
        <div class="bgimg">
            <div class="walking_john">
                <img src="/assets/walking-john.gif" width="454">
            </div>
          <div class="topleft">
            <p><img src="{{url('assets/logo.png')}}" width="180"></p>
          </div>
          <div class="middle">
            <h1>COMING SOON</h1>
            <hr>
            <p></p>
          </div>
          <div class="bottomleft">
            <p></p>
          </div>
        </div>
    </body>
</html>
