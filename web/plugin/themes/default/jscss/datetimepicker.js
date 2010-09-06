//Javascript name: My Date Time Picker
//Date created: 16-Nov-2003 23:19
//Scripter: TengYong Ng
//Website: http://www.rainforestnet.com
//Copyright (c) 2003 TengYong Ng
//FileName: DateTimePicker.js
//Version: 1.9
//Contact: contact@rainforestnet.com
// Note: Permission given to use and modify this script in ANY kind of applications if
//       header lines are left unchanged.
//Date changed: 24-Dec-2007 by Burgsoft (Holland)
//Changed: Year picker as drop down. Code optimised. Tables filled with blank fields as needed.
//Known (non fatal) issue: javascript remains running after month or year select

//Global variables
var winCal;
var dtToday=new Date();
var Cal;
// English
var MonthName=["January", "February", "March", "April", "May", "June","July", 
	"August", "September", "October", "November", "December"];
var WeekDayName1=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
var WeekDayName2=["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];

var exDateTime;//Existing Date and Time
var selDate;//selected date. version 1.7


//Configurable parameters
var cnTop="200";//top coordinate of calendar window.
var cnLeft="500";//left coordinate of calendar window
var WindowTitle ="DateTime Picker";//Date Time Picker title.
var WeekChar=2;//number of character for week day. if 2 then Mo,Tu,We. if 3 then Mon,Tue,Wed.
var CellWidth=30;//Width of day cell.
var DateSeparator="-";//Date Separator, you can change it to "/" if you want.
var TimeMode=24;//default TimeMode value. 12 or 24

var ShowLongMonth=true;//Show long month name in Calendar header. example: "January".
var ShowMonthYear=true;//Show Month and Year in Calendar header.
var MonthYearColor="#cc0033";//Font Color of Month and Year in Calendar header.
var WeekHeadColor="#0099CC";//Background Color in Week header.
var SundayColor="#6699FF";//Background color of Sunday.
var SaturdayColor="#CCCCFF";//Background color of Saturday.
var WeekDayColor="white";//Background color of weekdays.
var FontColor="blue";//color of font in Calendar day cell.
var TodayColor="#FFFF33";//Background color of today.
var SelDateColor="#FFFF99";//Backgrond color of selected date in textbox.
var YrSelColor="#cc0033";//color of font of Year selector.
var MthSelColor="#cc0033";//color of font of Month selector if "MonthSelector" is "arrow".
var ThemeBg="";//Background image of Calendar window.
var PrecedeZero=true;//Preceding zero [true|false]
var MondayFirstDay=false;//true:Use Monday as first day; false:Sunday as first day. [true|false]  //added in version 1.7
//end Configurable parameters
//end Global variable

function NewCal(pCtrl,pFormat,pShowTime,pTimeMode,pScroller,pHideSeconds)
{
	Cal=new Calendar(dtToday);
	if ((pShowTime!=null) && (pShowTime))
	{
		Cal.ShowTime=true;
		if ((pTimeMode!=null) &&((pTimeMode=='12')||(pTimeMode=='24')))
		{
			TimeMode=pTimeMode;
		}
		if ((pHideSeconds!=null)&&(pHideSeconds))
		{
			Cal.ShowSeconds=false;
		}		
	}	
	if (pCtrl!=null)
		Cal.Ctrl=pCtrl;
	if (pFormat!=null)
		Cal.Format=pFormat.toUpperCase();
	if (pScroller!=null)
	{
		if (pScroller.toUpperCase()=="ARROW")
			Cal.Scroller="ARROW";
		else
			Cal.Scroller="DROPDOWN";
    }		
	
	exDateTime=document.getElementById(pCtrl).value;
	if (exDateTime!="")//Parse existing Date String
	{
		var Sp1;//Index of Date Separator 1
		var Sp2;//Index of Date Separator 2 
		var tSp1;//Index of Time Separator 1
		var tSp1;//Index of Time Separator 2
		var strMonth;
		var strDate;
		var strYear;
		var intMonth;
		var YearPattern;
		var strHour;
		var strMinute;
		var strSecond;
		var winHeight;
		//parse month
		Sp1=exDateTime.indexOf(DateSeparator,0)
		Sp2=exDateTime.indexOf(DateSeparator,(parseInt(Sp1)+1));
		
		var offset=parseInt(Cal.Format.toUpperCase().lastIndexOf("M"))-parseInt(Cal.Format.toUpperCase().indexOf("M"))-1;
		if ((Cal.Format.toUpperCase()=="DDMMYYYY") || (Cal.Format.toUpperCase()=="DDMMMYYYY"))
		{
			if (DateSeparator=="")
			{
				strMonth=exDateTime.substring(2,4+offset);
				strDate=exDateTime.substring(0,2);
				strYear=exDateTime.substring(4+offset,8+offset);
			}
			else
			{
				strMonth=exDateTime.substring(Sp1+1,Sp2);
				strDate=exDateTime.substring(0,Sp1);
				strYear=exDateTime.substring(Sp2+1,Sp2+5);
			}
		}
		else if ((Cal.Format.toUpperCase()=="MMDDYYYY") || (Cal.Format.toUpperCase()=="MMMDDYYYY"))
		{
			if (DateSeparator=="")
			{
				strMonth=exDateTime.substring(0,2+offset);
				strDate=exDateTime.substring(2+offset,4+offset);
				strYear=exDateTime.substring(4+offset,8+offset);
			}
			else
			{
				strMonth=exDateTime.substring(0,Sp1);
				strDate=exDateTime.substring(Sp1+1,Sp2);
				strYear=exDateTime.substring(Sp2+1,Sp2+5);
			}

		}
		else if ((Cal.Format.toUpperCase()=="YYYYMMDD") || (Cal.Format.toUpperCase()=="YYYYMMMDD"))
		{
			if (DateSeparator=="")
			{
				strMonth=exDateTime.substring(4,6+offset);
				strDate=exDateTime.substring(6+offset,8+offset);
				strYear=exDateTime.substring(0,4);
			}
			else
			{
				strMonth=exDateTime.substring(Sp1+1,Sp2);
				strDate=exDateTime.substring(Sp2+1,Sp2+3);
				strYear=exDateTime.substring(0,Sp1);
			}
		}

		if (isNaN(strMonth))
			intMonth=Cal.GetMonthIndex(strMonth);
		else
			intMonth=parseInt(strMonth,10)-1;	
		if ((parseInt(intMonth,10)>=0) && (parseInt(intMonth,10)<12))
			Cal.Month=intMonth;
		//end parse month
		//parse Date
		if ((parseInt(strDate,10)<=Cal.GetMonDays()) && (parseInt(strDate,10)>=1))
			Cal.Date=strDate;
		//end parse Date
		//parse year
		YearPattern=/^\d{4}$/;
		if (YearPattern.test(strYear))
			Cal.Year=parseInt(strYear,10);
		//end parse year
		//parse time
		if (Cal.ShowTime==true)
		{
			//parse AM or PM
			if (TimeMode==12)
			{
				strAMPM=exDateTime.substring(exDateTime.length-2,exDateTime.length)
				Cal.AMorPM=strAMPM;
			}
			tSp1=exDateTime.indexOf(":",0)
			tSp2=exDateTime.indexOf(":",(parseInt(tSp1)+1));
			if (tSp1>0)
			{
				strHour=exDateTime.substring(tSp1,(tSp1)-2);
				Cal.SetHour(strHour);
				strMinute=exDateTime.substring(tSp1+1,tSp1+3);
				Cal.SetMinute(strMinute);
				strSecond=exDateTime.substring(tSp2+1,tSp2+3);
				Cal.SetSecond(strSecond);
			}
			//window.status=strHour+":"+strMinute+":"+strSecond;
		}	
	}
	selDate=new Date(Cal.Year,Cal.Month,Cal.Date);//version 1.7
	winCal=window.open("","DateTimePicker","toolbar=0,status=0,menubar=0,fullscreen=no,width=230,height=235,resizable=0,top="+cnTop+",left="+cnLeft);
	RenderCal();
	winCal.focus();
}

function RenderCal()
{
	var vCalHeader;
	var vCalData;
	var vCalTime;
	var i;
	var j;
	var SelectStr;
	var vDayCount=0;
	var vFirstDay;

	winCalData ="<html>\n<head>\n<title>"+WindowTitle+"</title>\n";
	winCalData+="<script type='text/javascript' language='javascript'>\n";
	winCalData+="var winMain=window.opener;\nfunction callback(id, datum) {\n"; 
	winCalData+=" var CalId = winMain.document.getElementById(id); CalId.value=datum;\n";
	winCalData+="if (winMain.Cal.ShowTime) {\n";
	winCalData+=" CalId.value+=' '+winMain.Cal.getShowHour()+':'+winMain.Cal.Minutes;\n";
	winCalData+=" if (winMain.Cal.ShowSeconds)\n  CalId.value+=':'+winMain.Cal.Seconds;\n";
	winCalData+=" if (winMain.TimeMode==12)\n  CalId.value+=' '+winMain.Cal.getShowAMorPM();\n";	
	winCalData+="}\n window.close();\n}\n";
	winCalData+="</script>\n";//winMain is window that opened calendar window.
	winCalData+="<style type='text/css'>\n td {font-family: verdana; font-size: 12Px; text-align: center; cursor: pointer;}\n .r {font-weight: bold; color: red;}\n</style>\n";
	winCalData+="</head>\n<body background='"+ThemeBg+"' link='"+FontColor+"' vlink='"+FontColor+"'>\n<form name='Calendar'>\n<div style='position: absolute; margin-left: auto; margin-right: auto;'>\n";
	winCal.document.open();
	winCal.document.writeln(winCalData);

	vCalHeader="<table border=1 cellpadding=1 cellspacing=1 width=\"200\" valign=\"top\">\n";
	//Table for Month & Year Selector
	vCalHeader+="<tr>\n<td colspan='7'>\n<table border=0 width=200 cellpadding=0 cellspacing=0>\n<tr>\n";

	//******************Month and Year selector in dropdown list************************
	if (Cal.Scroller=="DROPDOWN")
	{
		vCalHeader+="<td align='left'><select name=\"MonthSelector\" onChange=\"javascript:winMain.Cal.SwitchMth(this.selectedIndex);winMain.RenderCal();\">\n";
		for (i=0;i<12;i++)
		{
			if (i==Cal.Month)
				SelectStr="Selected";
			else
				SelectStr="";	
			vCalHeader+="<option "+SelectStr+" value="+i+">"+MonthName[i]+"</option>\n";
		}
		vCalHeader+="</select></td>\n";
		//Year selector
		vCalHeader+="<td align='right'><select name=\"YearSelector\" size=\"1\" onChange=\"javascript:winMain.Cal.SwitchYear(this.value);winMain.RenderCal();\">\n";
		for (i = 1950; i < (dtToday.getFullYear() + 5);i++)
		{
			if (i==Cal.Year)
				SelectStr="Selected";
			else
				SelectStr="";	
			vCalHeader+="<option "+SelectStr+" value="+i+">"+i+"</option>\n";
		}
		vCalHeader+="</select></td>\n";
	}
	//******************End Month and Year selector in dropdown list*********************
	//******************Month and Year selector in arrow*********************************
	else if (Cal.Scroller=="ARROW")
	{
		vCalHeader+="<td><a href='javascript:winMain.Cal.DecYear();winMain.RenderCal();'>- </a></td>\n";//Year scroller (decrease 1 year)
		vCalHeader+="<td><a href='javascript:winMain.Cal.DecMonth();winMain.RenderCal();'>&lt;</a></td>\n";//Month scroller (decrease 1 month)
		vCalHeader+="<td width='70%'><font color='"+YrSelColor+"'><b>"+Cal.GetMonthName(ShowLongMonth)+" "+Cal.Year+"</b></font></td>\n"//Month and Year
		vCalHeader+="<td><a href='javascript:winMain.Cal.IncMonth();winMain.RenderCal();'>&gt;</a></td>\n";//Month scroller (increase 1 month)
		vCalHeader+="<td><a href='javascript:winMain.Cal.IncYear();winMain.RenderCal();'>+</a></td>\n";//Year scroller (increase 1 year)
	}
	vCalHeader+="</tr>\n</table>\n</td></tr>\n"
  //******************End Month and Year selector in arrow******************************
	//Calendar header shows Month and Year
	if ((ShowMonthYear)&&(Cal.Scroller=="DROPDOWN"))
		vCalHeader+="<tr><td colspan='7'>\n<font  class='r' color='"+MonthYearColor+"'>"+Cal.GetMonthName(ShowLongMonth)+" "+Cal.Year+"</font>\n</td></tr>\n";
	//Week day header
	vCalHeader+="<tr bgcolor="+WeekHeadColor+">\n";
	var WeekDayName=new Array();//Added version 1.7
	if (MondayFirstDay==true)
		WeekDayName=WeekDayName2;
	else
		WeekDayName=WeekDayName1;
	for (i=0;i<7;i++)
	{
		vCalHeader+="<td width="+CellWidth+">\n"+WeekDayName[i].substr(0,WeekChar)+"</td>\n";
	}
	
	vCalHeader+="</tr>\n";	
	winCal.document.write(vCalHeader);
	//Calendar detail
	CalDate=new Date(Cal.Year,Cal.Month);
	CalDate.setDate(1);
	vFirstDay=CalDate.getDay();
	//Added version 1.7
	if (MondayFirstDay==true)
	{
		vFirstDay-=1;
		if (vFirstDay==-1)
			vFirstDay=6;
	}
	//Added version 1.7
	vCalData="<tr>";
	for (i=0;i<vFirstDay;i++)
	{
		vCalData=vCalData+GenCell();
		vDayCount=vDayCount+1;
	}
	//Added version 1.7
	for (j=1;j<=Cal.GetMonDays();j++)
	{
		var strCell;
		if((vDayCount%7==0)&&(j > 1))
		{
			vCalData=vCalData+"<tr>\n";
		}
		vDayCount=vDayCount+1;
		if ((j==dtToday.getDate())&&(Cal.Month==dtToday.getMonth())&&(Cal.Year==dtToday.getFullYear()))
			strCell=GenCell(j,true,TodayColor);//Highlight today's date
		else
		{
			if ((j==selDate.getDate())&&(Cal.Month==selDate.getMonth())&&(Cal.Year==selDate.getFullYear()))//modified version 1.7
			{
				strCell=GenCell(j,true,SelDateColor);
			}
			else
			{	
				if (MondayFirstDay==true)
				{
					if (vDayCount%7==0)
						strCell=GenCell(j,false,SundayColor);
					else if ((vDayCount+1)%7==0)
						strCell=GenCell(j,false,SaturdayColor);
					else
						strCell=GenCell(j,null,WeekDayColor);					
				} 
				else
				{
					if (vDayCount%7==0)
						strCell=GenCell(j,false,SaturdayColor);
					else if ((vDayCount+6)%7==0)
						strCell=GenCell(j,false,SundayColor);
					else
						strCell=GenCell(j,null,WeekDayColor);
				}
			}		
		}						
		vCalData=vCalData+strCell;

		if((vDayCount%7==0)&&(j<Cal.GetMonDays()))
		{
			vCalData=vCalData+"</tr>\n";
		}
	}
	// finish the table proper
	if(!(vDayCount%7) == 0) 
	{
		while(!(vDayCount % 7) == 0) 
		{
			vCalData=vCalData+GenCell();
			vDayCount=vDayCount+1;
		}
		vCalData=vCalData+"</tr>\n";
	}
		
	winCal.document.writeln(vCalData);	
	//Time picker
	if (Cal.ShowTime)
	{
		var showHour;
		showHour=Cal.getShowHour();	
		vCalTime="<tr>\n<td colspan='7' align='center'>\n";
		vCalTime+="<input type='text' name='hour' maxlength=2 size=1 style=\"WIDTH: 22px\" value="+showHour+" onChange=\"javascript:winMain.Cal.SetHour(this.value)\">\n";
		vCalTime+=" : ";
		vCalTime+="<input type='text' name='minute' maxlength=2 size=1 style=\"WIDTH: 22px\" value="+Cal.Minutes+" onChange=\"javascript:winMain.Cal.SetMinute(this.value)\">\n";
		if (Cal.ShowSeconds)
		{
			vCalTime+=" : ";
			vCalTime+="<input type='text' name='second' maxlength=2 size=1 style=\"WIDTH: 22px\" value="+Cal.Seconds+" onChange=\"javascript:winMain.Cal.SetSecond(this.value)\">\n";
		}
		if (TimeMode==12)
		{
			var SelectAm =(Cal.AMorPM=="AM")? "Selected":"";
			var SelectPm =(Cal.AMorPM=="PM")? "Selected":"";

			vCalTime+="<select name=\"ampm\" onChange=\"javascript:winMain.Cal.SetAmPm(this.options[this.selectedIndex].value);\">\n";
			vCalTime+="<option "+SelectAm+" value=\"AM\">AM</option>";
			vCalTime+="<option "+SelectPm+" value=\"PM\">PM<option>";
			vCalTime+="</select>";
		}	
		vCalTime+="\n</td>\n</tr>";
		winCal.document.write(vCalTime);
	}	
	//end time picker
	winCal.document.writeln("\n</table>\n");
	winCal.document.writeln("</div>\n</form>\n</body>\n</html>");
	winCal.document.close();
	return true;
}

function GenCell(pValue,pHighLight,pColor)//Generate table cell with value
{
	var PValue;
	var PCellStr;
	var vColor;
	var vHLstr1;//HighLight string
	var vHlstr2;
	var vTimeStr;
	
	if (pValue==null)
		PValue="";
	else
		PValue=pValue;
	
	if (pColor!=null)
		vColor="bgcolor=\""+pColor+"\"";
	else
		vColor="";	
	if ((pHighLight!=null)&&(pHighLight))
		{vHLstr1="<font class='r'>";vHLstr2="</font>";}
	else
		{vHLstr1="";vHLstr2="";}	
	
	if (Cal.ShowTime)
	{
		vTimeStr=' '+Cal.Hours+':'+Cal.Minutes;
		if (Cal.ShowSeconds)
			vTimeStr+=':'+Cal.Seconds;
		if (TimeMode==12)
			vTimeStr+=' '+Cal.AMorPM;
	}	
	else
		vTimeStr="";		
	if (PValue!="")
		PCellStr="<td "+vColor+" onClick=\"javascript:callback('"+Cal.Ctrl+"','"+Cal.FormatDate(PValue)+"');\">"+vHLstr1+PValue+vHLstr2+"</td>\n";
	else
		PCellStr="<td "+vColor+">&nbsp;</td>\n";
	return PCellStr;
}

function Calendar(pDate,pCtrl)
{
	//Properties
	this.Date=pDate.getDate();//selected date
	this.Month=pDate.getMonth();//selected month number
	this.Year=pDate.getFullYear();//selected year in 4 digits
	this.Hours=pDate.getHours();	
	
	if (pDate.getMinutes()<10)
		this.Minutes="0"+pDate.getMinutes();
	else
		this.Minutes=pDate.getMinutes();
	
	if (pDate.getSeconds()<10)
		this.Seconds="0"+pDate.getSeconds();
	else		
		this.Seconds=pDate.getSeconds();
		
	this.MyWindow=winCal;
	this.Ctrl=pCtrl;
	this.Format="ddMMyyyy";
	this.Separator=DateSeparator;
	this.ShowTime=false;
	this.Scroller="DROPDOWN";
	if (pDate.getHours()<12)
		this.AMorPM="AM";
	else
		this.AMorPM="PM";
	this.ShowSeconds=true;		
}

function GetMonthIndex(shortMonthName)
{
	for (i=0;i<12;i++)
	{
		if (MonthName[i].substring(0,3).toUpperCase()==shortMonthName.toUpperCase())
		{	return i;}
	}
}
Calendar.prototype.GetMonthIndex=GetMonthIndex;

function IncYear()
{	Cal.Year++;}
Calendar.prototype.IncYear=IncYear;

function DecYear()
{	Cal.Year--;}
Calendar.prototype.DecYear=DecYear;

function IncMonth()
{	
	Cal.Month++;
	if (Cal.Month>=12)
	{
		Cal.Month=0;
		Cal.IncYear();
	}
}
Calendar.prototype.IncMonth=IncMonth;

function DecMonth()
{	
	Cal.Month--;
	if (Cal.Month<0)
	{
		Cal.Month=11;
		Cal.DecYear();
	}
}
Calendar.prototype.DecMonth=DecMonth;
	
function SwitchMth(intMth)
{	Cal.Month=intMth;}
Calendar.prototype.SwitchMth=SwitchMth;

function SwitchYear(intYear)
{	Cal.Year=intYear;}
Calendar.prototype.SwitchYear=SwitchYear;

function SetHour(intHour)
{	
	var MaxHour;
	var MinHour;
	if (TimeMode==24)
	{	MaxHour=23;MinHour=0}
	else if (TimeMode==12)
	{	MaxHour=12;MinHour=1}
	else
		alert("TimeMode can only be 12 or 24");		
	var HourExp=new RegExp("^\\d\\d");
	var SingleDigit=new RegExp("\\d");
	//alert(SingleDigit.test(intHour));
	if (SingleDigit.test(intHour))
	{
		intHour="0"+intHour+"";
		//alert(intHour);	
	}	
	if (HourExp.test(intHour) && (parseInt(intHour,10)<=MaxHour) && (parseInt(intHour,10)>=MinHour))
	{	
		if ((TimeMode==12) && (Cal.AMorPM=="PM"))
		{
			if (parseInt(intHour,10)==12)
				Cal.Hours=12;
			else	
				Cal.Hours=parseInt(intHour,10)+12;
		}	
		else if ((TimeMode==12) && (Cal.AMorPM=="AM"))
		{
			if (intHour==12)
				intHour-=12;
			Cal.Hours=parseInt(intHour,10);
		}
		else if (TimeMode==24)
			Cal.Hours=parseInt(intHour,10);	
	}
}
Calendar.prototype.SetHour=SetHour;

function SetMinute(intMin)
{
	var MinExp=new RegExp("^\\d\\d$");
	if (MinExp.test(intMin) && (intMin<60))
		Cal.Minutes=intMin;
}
Calendar.prototype.SetMinute=SetMinute;

function SetSecond(intSec)
{	
	var SecExp=new RegExp("^\\d\\d$");
	if (SecExp.test(intSec) && (intSec<60))
		Cal.Seconds=intSec;
}
Calendar.prototype.SetSecond=SetSecond;

function SetAmPm(pvalue)
{
	this.AMorPM=pvalue;
	if (pvalue=="PM")
	{
		this.Hours=(parseInt(this.Hours,10))+12;
		if (this.Hours==24)
			this.Hours=12;
	}	
	else if (pvalue=="AM")
		this.Hours-=12;	
}
Calendar.prototype.SetAmPm=SetAmPm;

function getShowHour()
{
	var finalHour;
    if (TimeMode==12)
    {
    	if (parseInt(this.Hours,10)==0)
		{
			this.AMorPM="AM";
			finalHour=parseInt(this.Hours,10)+12;	
		}
		else if (parseInt(this.Hours,10)==12)
		{
			this.AMorPM="PM";
			finalHour=12;
		}		
		else if (this.Hours>12)
		{
			this.AMorPM="PM";
			if ((this.Hours-12)<10)
				finalHour="0"+((parseInt(this.Hours,10))-12);
			else
				finalHour=parseInt(this.Hours,10)-12;	
		}
		else
		{
			this.AMorPM="AM";
			if (this.Hours<10)
				finalHour="0"+parseInt(this.Hours,10);
			else
				finalHour=this.Hours;	
		}
	}
	else if (TimeMode==24)
	{
		if (this.Hours<10)
			finalHour="0"+parseInt(this.Hours,10);
		else	
			finalHour=this.Hours;
	}	
	return finalHour;	
}				
Calendar.prototype.getShowHour=getShowHour;		

function getShowAMorPM()
{
	return this.AMorPM;	
}				
Calendar.prototype.getShowAMorPM=getShowAMorPM;		

function GetMonthName(IsLong)
{
	var Month=MonthName[this.Month];
	if (IsLong)
		return Month;
	else
		return Month.substr(0,3);
}
Calendar.prototype.GetMonthName=GetMonthName;

function GetMonDays()//Get number of days in a month
{
	var DaysInMonth=[31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	if (this.IsLeapYear())
	{
		DaysInMonth[1]=29;
	}	
	return DaysInMonth[this.Month];	
}
Calendar.prototype.GetMonDays=GetMonDays;

function IsLeapYear()
{
	if ((this.Year%4)==0)
	{
		if ((this.Year%100==0) && (this.Year%400)!=0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	else
	{
		return false;
	}
}
Calendar.prototype.IsLeapYear=IsLeapYear;

function FormatDate(pDate)
{
	var MonthDigit=this.Month+1;
	if (PrecedeZero==true)
	{
		if (pDate<10)
			pDate="0"+pDate;
		if (MonthDigit<10)
			MonthDigit="0"+MonthDigit;
	}

	if (this.Format.toUpperCase()=="DDMMYYYY")
		return (pDate+DateSeparator+MonthDigit+DateSeparator+this.Year);
	else if (this.Format.toUpperCase()=="DDMMMYYYY")
		return (pDate+DateSeparator+this.GetMonthName(false)+DateSeparator+this.Year);
	else if (this.Format.toUpperCase()=="MMDDYYYY")
		return (MonthDigit+DateSeparator+pDate+DateSeparator+this.Year);
	else if (this.Format.toUpperCase()=="MMMDDYYYY")
		return (this.GetMonthName(false)+DateSeparator+pDate+DateSeparator+this.Year);
	else if (this.Format.toUpperCase()=="YYYYMMDD")
		return (this.Year+DateSeparator+MonthDigit+DateSeparator+pDate);
	else if (this.Format.toUpperCase()=="YYYYMMMDD")
		return (this.Year+DateSeparator+this.GetMonthName(false)+DateSeparator+pDate);	
	else					
		return (pDate+DateSeparator+(this.Month+1)+DateSeparator+this.Year);
}
Calendar.prototype.FormatDate=FormatDate;	