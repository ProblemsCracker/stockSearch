<?php 

function getStock(){#get stock price and news
    $companySymbol = $_GET["symbol"];
    #$companySymbol = htmlentities($companySymbol, ENT_QUOTES, 'UTF-8');
    #$companySymbol = htmlspecialchars($companySymbol, ENT_QUOTES);
    #htmlspecialchars("<a href='test'>Test</a>", ENT_QUOTES)
    #echo $_GET["companyName"];
    #handle false info
        if($companySymbol == ""){
            echo "<script/type='text/javascript'>alert('Please enter a company symbol.')</script>";
        }
        else{
            #echo "symbol is ".$companySymbol;
            #handle proper info

            $complete_url_stock = 'http://query.yahooapis.com/v1/public/yql?'
                    . 'q=Select%20Name%2C%20Symbol%2C%20LastTradePriceOnly'
                    . '%2C%20Change%2C%20ChangeinPercent%2C%20PreviousClose'
                    . '%2C%20DaysLow%2C%20DaysHigh%2C%20Open%2C%20YearLow%2C'
                    . '%20YearHigh%2C%20Bid%2C%20Ask%2C%20AverageDailyVolume%2C'
                    . '%20OneyrTargetPrice%2C%20MarketCapitalization%2C%20Volume%2'
                    . 'C%20Open%2C%20YearLow%20from%20yahoo.finance.quotes%20where%20'
                    . 'symbol%3D%22'
                    . $companySymbol #companyName
                    . '%22&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';

            $xmlDoc_stock = simplexml_load_file(urlencode($complete_url_stock));

            #echo $xmlDoc_stock;
            
            $complete_url_news = 'http://feeds.finance.yahoo.com/rss/2.0/headline?s='
                    . $companySymbol
                    . '&region=US&lang=en-US';

            $xmlDoc_news = simplexml_load_file(urlencode($complete_url_news));

            $StockChartImageURL = 'http://chart.finance.yahoo.com/t?s='
                    . urlencode($companySymbol)
                    .'&amp;lang=en-US&amp;amp;width=300&amp;height=180';
  
            parseXML($xmlDoc_stock, $xmlDoc_news, $companySymbol, $StockChartImageURL); 
        }
}


function parseXML($xmlDoc_stock, $xmlDoc_news, $companySymbol, $StockChartImageURL){

    #start writing stock info
    

    if(isset($xmlDoc_stock) && isset($xmlDoc_stock->children()[0]) && isset($xmlDoc_stock->children()[0]->children()[0]) 
            && isset($xmlDoc_stock->children()[0]->children()[0]->children()[0])
            && floatval($xmlDoc_stock->children()[0]->children()[0]->children()->LastTradePriceOnly) != 0){#judge if there is info exists
                #print_r($xmlDoc);
                #echo "<br/>";

     
        $level1_children = $xmlDoc_stock->children();
        $level2_children = $level1_children[0]->children();        
        
        $resultXML = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><result></result>");
       
        
        $resultXML->addChild('Name');
        $resultXML->Name = $level2_children->children()->Name;
        
        $resultXML->addChild('Symbol');
        $resultXML->Symbol = $companySymbol;
        
        $quote = $resultXML->addChild('Quote');
        
        $Change = $level2_children->children()->Change;
        $Change = floatval($Change);
        $ChangeinPercent = $level2_children->children()->ChangeinPercent;
        $ChangeinPercent = floatval($ChangeinPercent);

        $quote->addChild('ChangeType');
        if($ChangeinPercent < 0){
            $quote->ChangeType = '-';
        }
        else if($ChangeinPercent > 0){ 
            $quote->ChangeType = '+';
        }

        $quote->addChild('Change');
        $quote->Change = number_format(floatval(substr($Change, 1)), 2);
        
        $quote->addChild('ChangeInPercent');
        $quote->ChangeInPercent = $ChangeinPercent.'%';
        
        $quote->addChild('LastTradePriceOnly');
        $quote->LastTradePriceOnly = number_format(floatval($level2_children->children()->LastTradePriceOnly), 2);
        
        $quote->addChild('PreviousClose');
        $quote->PreviousClose = number_format(floatval($level2_children->children()->PreviousClose),2);

        $quote->addChild('DaysLow');
        $quote->DaysLow = number_format(floatval($level2_children->children()->DaysLow),2);
        
        $quote->addChild('DaysHigh');
        $quote->DaysHigh = number_format(floatval($level2_children->children()->DaysHigh),2);

        $quote->addChild('Open');
        $quote->Open = number_format(floatval($level2_children->children()->Open),2);

        $quote->addChild('YearLow');
        $quote->YearLow = number_format(floatval($level2_children->children()->YearLow),2);
         
        $quote->addChild('YearHigh');
        $quote->YearHigh = number_format(floatval($level2_children->children()->YearHigh),2);

        $quote->addChild('Bid');
        $quote->Bid = number_format(floatval($level2_children->children()->Bid),2);

        $quote->addChild('Volume');
        $quote->Volume = number_format(floatval($level2_children->children()->Volume),2);

        $quote->addChild('Ask');
        $quote->Ask = number_format(floatval($level2_children->children()->Ask),2);

        $quote->addChild('AverageDailyVolume');
        $quote->AverageDailyVolume = number_format(floatval($level2_children->children()->AverageDailyVolume),2);

        $quote->addChild('OneYearTargetPrice');
        $quote->OneYearTargetPrice = number_format(floatval($level2_children->children()->OneyrTargetPrice),2);

        $marketCap_str = $level2_children->children()->MarketCapitalization;
        $marketCapUnit = substr($marketCap_str, -1);

        $quote->addChild('MarketCapitalization');
        $quote->MarketCapitalization = number_format(floatval($level2_children->children()->MarketCapitalization),2).$marketCapUnit;

        #end of writing stock info

        #start writing news

        $level1_children = $xmlDoc_news->children();
        $item = $level1_children->children()->item;
        
        if(isset($xmlDoc_news) && $level1_children->count() != 0){
         
            $news = $resultXML->addChild('News');

            foreach($item as $every_item)
            {
                $Item = $news->addChild('Item');
                
                $Item->addChild('Title');
                $Item->Title = htmlentities($every_item->title, ENT_QUOTES, 'UTF-8') ;
                
           
                $Item->addChild('Link');
                $Item->Link = $every_item->link;
            }
            
            $resultXML->addChild('StockChartImageURL');
            $resultXML->StockChartImageURL = $StockChartImageURL;
            
         }
         
         Header('Content-type: text/xml');
         Header('Charset=UTF-8');

         echo $resultXML->asXML();
    #end of writing news

   }
   else{
       Header('Content-type: text/xml');
       Header('Charset=UTF-8');
       $resultXML = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><error>Stock Information Not Available</error>");
       echo $resultXML->asXML();
   }
} 


//***********************************//
//main function
//***********************************//
#echo "<br>test!!<br>";

if(isset($_GET["symbol"])){
    #echo "pass back to java servlet <br>";
    #echo "the company name is: ". $_GET["symbol"];
    getStock();
}

?>