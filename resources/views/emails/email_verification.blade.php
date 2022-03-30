<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
      <meta content="width=device-width; initial-scale=1.0" name="viewport" />
      <meta name="x-apple-disable-message-reformatting" />
      <title>TownBuddy</title>
      <style type="text/css">
         @import url(http://fonts.googleapis.com/css?family=Muli:300,400,700);
         html, html a {
         -webkit-font-smoothing:antialised !important;
         }
         body {
         margin: 10px 0;
         padding: 0 10px;
         background:#666;
         font-size: 13px;
         font-family:'Muli',Arial, Helvetica, Sans-serif !important;
         }
         a {
         color:#38b9e2;
         text-decoration:none;	
         }
         table {
         border-collapse: collapse;
         }
         td {
         font-family:'Muli',Arial, Helvetica, Sans-serif !important;
         color: #333333;
         }
         @media only screen and (max-width: 480px) {
         body, table, td, p, a, li, blockquote {
         -webkit-text-size-adjust:none !important;
         }
         table {
         width: 100% !important;
         }
         td[class="w5"], img[class="w5"] {
         width: 5px!important;
         }
         .responsive img {
         height: auto !important;
         max-width: 100% !important;
         width: 100% !important;
         }
         table.button {
         width:50% !important;
         }
         table.button-top {
         width:60% !important;
         }
         td.send-button { 
         background:#999 !important;
         width:35% !important;
         }
         td.send-button a { 
         color:#fff !important;
         }
         td.social-button a {	
         font-size:12px !important;
         }
         td.headline {
         font-size:16px !important;
         }
         td.sub-heading {
         font-size:15px !important;
         }
         table {
         position: relative;
         }
         a[class="mobileshow"], a[class="mobilehide"] {
         display: block !important;
         color: #fff !important;
         background-color: #38b9e2;
         border-radius: 20px;
         padding:5px 10px;
         text-decoration: none;
         font-weight: bold;
         font-size: 13px;
         position: absolute;
         top: 25px;
         right: 20px;
         text-align: center;
         width: 40px;
         }
         div[class="article"] {
         display: none;
         }
         a.mobileshow:hover {
         visibility: hidden;
         }
         .mobileshow:hover + .article, .article:hover {
         display: inline !important;
         }
         div[class="header"] {
         font-size: 16px !important;
         }
         table[class="table"], td[class="cell"] {
         width: 320px !important;
         }
         td[class="footershow"] {
         width: 320px !important;
         padding-left: 25px;
         padding-right: 25px;
         }
         table[class="hide"], img[class="hide"], td[class="hide"] {
         display: none !important;
         }
         img[class="divider"] {
         height: 1px !important;
         }
         p[class="reminder"] {
         font-size: 11px !important;
         }
         h4[class="secondary"] {
         line-height: 22px !important;
         margin-bottom: 15px !important;
         font-size: 18px !important;
         }
         td.connect { 
         display:none !important;
         }
         td.social-button {
         font-weight: 600 !important;
         line-height: 3;
         width:30% !important;
         }
         td.social-container {
         }
         *[class="w350"] {
         width: 350px!important;
         }
         *[class="stack"] {
         width: 350px!important;
         display:block !important;
         }
         *[class="hide"] {
         display:none !important;
         }
         *[class="mobile_width"] {
         width:200px !important;
         }
         *[class="mobileswap1"] {
         width: 190px!important;
         height:auto !important;
         }
         *[class="mobileswap2"] {
         width: 220px!important;
         height:auto !important;
         }
         *[class="mobileswap3"] {
         width: 170px!important;
         height:auto !important;
         }
         }
      </style>
   </head>
   <body style="background-color:#ffffff;background-image:none;background-repeat:repeat;background-position:top left;background-attachment:scroll;font-size:13px;font-family:'Muli' !important;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
      <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border-spacing:0;" >
         <tr>
            <td style="font-family:'Muli',Arial, Helvetica, Sans-serif !important;color:#333333;border-collapse:collapse;" >
               <table border="0" cellpadding="0" cellspacing="0" align="center" width="640" bgcolor="#ffffff" style="border-collapse:collapse;border-spacing:0;" >
                  <tr>
                     <td align="center" style="font-family:'Muli',Arial, Helvetica, Sans-serif !important;color:#333333;" >
                        <table width="80%" align="center" cellpadding="0" cellspacing="0" border="0" bgcolor="#f7f7f7" style="border-collapse:collapse;" >
                           <tr>
                              <td  bgcolor="#ffffff"  height="20" style="font-size:20px;line-height:1px;font-family:'Muli',Arial, Helvetica, Sans-serif !important;color:#333333;" >&nbsp;</td>
                           </tr>
                           
                           <tr>
                              <td align="center" width="80%" bgcolor="#f7f7f7" height="30" style="font-size:30px;line-height:1px;font-family:'Muli',Arial, Helvetica, Sans-serif !important;color:#333333;" ></td>
                           </tr>
                           <tr>
                              <td align="center" bgcolor="#f7f7f7" style="line-height:25px;font-size:15px;font-weight:200;font-family:'Muli',Arial, Helvetica, Sans-serif !important;color:#333333;border-collapse:collapse;" >
                                 <table cellpadding="0" cellspacing="0" align="center" width="80%" border="0"  class="w350" style="border-collapse:collapse;" >
                                    <tr>
                                       <td class="w350" style="font-family:'Muli',Arial, Helvetica, Sans-serif !important;color:#333333;" >
                                            <p>Dear {{$name}},</p>
                                            <p>Greetings! </p>
                                            <p>Town Buddy allows only verified users of senders/travelers/shoppers. Please verify your Town Buddy profile (contact No {{$mobile_number}} and Name: {{$name}}) with your email ID to use our platform. <p>
                                            <h3 style="color:#1dcae0"><strong><b>{{$otp}}</b></strong></h3>
                                            <p> Open the Town Buddy platform and enter the above code by tapping on Verify Now option in the Profile section to complete the verification process</p>
                                            <p> If this account does not belong to you, or if you have not registered it, please report back immediately at support@townbuddytravel.com</p>
                                            <p> Visit our website for more</p>

                                            <p>information,</p>
                                            
                                            <p>www.townbuddytravel.com</p>
                                            
                                            <p>For support,</p>
                                            
                                            <p>mail support@townbuddytravel.com</p>
                                            
                                            <p>Happy Shipping and earn Traveler Rewards by delivering shipments of verified users!</p>
                                            
                                            <p><strong><b>Team Town Buddy</b></strong></p>
                                       </td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                           <tr>
                              <td bgcolor="#f7f7f7" height="40" style="font-size:40px;line-height:1px;font-family:'Muli',Arial, Helvetica, Sans-serif !important;color:#333333;" ></td>
                           </tr>
                           <tr>
                              <td bgcolor="#ffffff" align="center" >
                                 <table cellpadding="0" class="mobile_width" cellspacing="0" width="50%" bgcolor="#efefef" >
                                 </table>
                              </td>
                           </tr>
                           <tr>
                              <td align="center" width="80%" bgcolor="#ffffff" height="20" style="font-size:20px;line-height:1px;font-family:'Muli',Arial, Helvetica, Sans-serif !important;color:#333333;" ></td>
                           </tr>
                           <tr>
                              <td bgcolor="#ffffff" align="center" style="font-family:'Muli',Arial, Helvetica, Sans-serif !important;color:#333333;" >
                                 <table cellpadding="0" cellspacing="0" align="center" width="580" border="0" class="w350" style="border-collapse:collapse;" >
                                    <tr>
                                       <!--td align="center" style="color:#000000;line-height:20px;font-size:13px;font-family:'Muli',Arial, Helvetica, Sans-serif !important;border-collapse:collapse;text-align:center;" >
                                          This email has been sent to you by TownBuddy.
                                       </td-->
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
      </table>
   </body>
</html>