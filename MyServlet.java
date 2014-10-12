import java.io.*;
import java.net.URL;
import java.net.URLConnection;
import java.util.Iterator;
import java.util.List;

import javax.servlet.*;
import javax.servlet.http.*;

import org.jdom.Document;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.input.SAXBuilder;
import org.json.JSONArray;
import org.json.JSONObject;


public class MyServlet extends HttpServlet {
	public void doGet(HttpServletRequest request, HttpServletResponse response)
    throws IOException, ServletException
    {  
        PrintWriter out = response.getWriter();
        String symbol = request.getParameter("symbol");

    	//URL url = new URL("http://cs-server.usc.edu:11672/index.php?symbol=" + symbol); 
    	URL url = new URL("http://wenbolinmyfirstelasticbeans-env.elasticbeanstalk.com/?symbol=" + symbol); 
    	/*
    	 * get XML file from URL
    	 */
    	Document xmlDocument = getXML(url, out);
    	
    	response.setContentType("application/json; charset=utf-8");
    	
    	//error check
    	if(xmlDocument == null){
    		JSONObject resultJSON = new JSONObject();
    		resultJSON.put("error", "Stock Information Not Available");
    		out.print(resultJSON);
    	}
    	/*
    	 * parse convertXMLtoJSON
    	 */
    	else{
	    	JSONObject resultJSON = convertXMLtoJSON(xmlDocument);
	
	        //out.println("symbol sent from html is :" + symbol + "<br>");
	        
	        out.print(resultJSON);
    	}
    	
     }
    
	public static Document getXML(URL url, PrintWriter out) throws IOException{
		
		URLConnection urlConnection = url.openConnection(); 
    	urlConnection.setAllowUserInteraction(false); 
    	InputStream urlStream = url.openStream();
        BufferedReader reader = new BufferedReader(new InputStreamReader(urlStream,"utf-8"));
		String lines;  
		String xml = "";
		while ((lines = reader.readLine()) != null){  
			xml += lines;
		} 
		//out.println("xml: " + xml);
		reader.close();  

		SAXBuilder saxBuilder = new SAXBuilder();  
		
		Document doc = new Document();
		try {
			doc = saxBuilder.build(new StringReader(xml));
		} catch (JDOMException e) {
			// TODO Auto-generated catch block
			//return e.toString();
		}
		Element result = doc.getRootElement();
		//out.println("CompanyName" + result.getChild("Name").getText());
		
		if(result.getText().equals("Stock Information Not Available"))
		{
			//out.print("result.getText():  " + result.getText());
			return null;
		}
		
		return doc;
		
	}
	
	public static JSONObject convertXMLtoJSON(Document xmlDocument){
		
		Element xmlRoot = xmlDocument.getRootElement();
		
		JSONObject resultJSON = new JSONObject();
		JSONObject resultEle = new JSONObject();
		resultJSON.put("result", resultEle);
		
		resultEle.put("Name", xmlRoot.getChild("Name").getText());
		resultEle.put("Symbol", xmlRoot.getChild("Symbol").getText());
		
		JSONObject quoteEle = new JSONObject();
		Element xmlQuote = xmlRoot.getChild("Quote");
		resultEle.put("Quote", quoteEle);
		quoteEle.put("ChangeType", xmlQuote.getChild("ChangeType").getText());
		quoteEle.put("Change", xmlQuote.getChild("Change").getText());
		quoteEle.put("ChangeInPercent", xmlQuote.getChild("ChangeInPercent").getText());
		quoteEle.put("LastTradePriceOnly", xmlQuote.getChild("LastTradePriceOnly").getText());
		quoteEle.put("Open", xmlQuote.getChild("Open").getText());
		quoteEle.put("YearLow", xmlQuote.getChild("YearLow").getText());
		quoteEle.put("YearHigh", xmlQuote.getChild("YearHigh").getText());
		quoteEle.put("Volume", xmlQuote.getChild("Volume").getText());
		quoteEle.put("OneYearTargetPrice", xmlQuote.getChild("OneYearTargetPrice").getText());
		quoteEle.put("Bid", xmlQuote.getChild("Bid").getText());
		quoteEle.put("DaysLow", xmlQuote.getChild("DaysLow").getText());
		quoteEle.put("DaysHigh", xmlQuote.getChild("DaysHigh").getText());
		quoteEle.put("Ask", xmlQuote.getChild("Ask").getText());
		quoteEle.put("AverageDailyVolume", xmlQuote.getChild("AverageDailyVolume").getText());
		quoteEle.put("PreviousClose", xmlQuote.getChild("PreviousClose").getText());
		quoteEle.put("MarketCapitalization", xmlQuote.getChild("MarketCapitalization").getText());
		
		JSONObject newsEle = new JSONObject();
		Element xmlNews = xmlRoot.getChild("News");
		List xmlItems = xmlNews.getChildren("Item");
		JSONArray itemArray = new JSONArray();
		
		resultEle.put("News", newsEle);
		
		for(int i = 0; i < xmlItems.size(); i++) {
			Element xmlSingleItem = (Element)xmlItems.get(i);
			
			JSONObject itemEle = new JSONObject();
			
			itemEle.put("Title", xmlSingleItem.getChild("Title").getText());
			itemEle.put("Link", xmlSingleItem.getChild("Link").getText());
			itemArray.put(itemEle);
		}
		
		newsEle.put("Item", itemArray);
		
		resultEle.put("StockChartImageURL", xmlRoot.getChild("StockChartImageURL").getText());
		
		return resultJSON;
	}
	
}