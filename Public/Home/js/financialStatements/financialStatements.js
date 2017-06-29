var getOption = function(chartType) {
	var chartOption = {
		legend: {
			data: ['充值', '提现']
		},
		grid: {
			x: 30,
			x2: 15,
			y: 25,
			y2: 25
		},
		toolbox: {//工具盒子（保存图片等工具）
			show:false,
			feature: {
				mark: {
					show: true
				},
				dataView: {
					show: true,
					readOnly: false
				},
				magicType: {
					show: true,
					type: ['line', 'bar']
				},
				restore: {
					show: true
				},
				saveAsImage: {
					show: true
				}
			}
		},
		calculable: false,

		xAxis: [{
			type: 'category',
			data: ['1','2','3','4','5','6','7']
		}],
		yAxis: [{
			type: 'value',
			splitArea: {
				show: true
			}
		}],
		series: [{
			name: '充值',
			type: chartType,
			data: [10, 20, 50, 30, 10, 80, 100]

		}, {
			name: '提现',
			type: chartType,
			data: [100, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6]
		}]
	};
	//TODO 通过ajax获取到数据，转化为对应的数组，并赋值给对应的x，y轴，即可完成数据动态加载显示图标；


        var xmlhttp;
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.open("POST","http://localhost/home/user/financialStatements?",true);
        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");//需要加表头
        //xmlhttp.send("&shopid=" + $('#inputid').val());

        xmlhttp.onreadystatechange=function()//服务器响应，上面的user自定义名，id是值
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                var respone = xmlhttp.responseText;//获得服务器返回值
                alert(respone);
            }
        }







    //var x= ['1','2','3','4','5','6','7'];
    // chartOption.xAxis[0]['data']=x;			//传递给x轴
    // chartOption.series[0]['data']=recharge;	//传递给充值
    // chartOption.series[1]['data']=withdraw;	//传递给提现
	return chartOption;
};
var byId = function(id) {
	return document.getElementById(id);
};
var lineChart = echarts.init(byId('lineChart'));
lineChart.setOption(getOption('line'));