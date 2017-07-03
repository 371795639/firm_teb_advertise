var getOption = function(chartType) {
	var chartOption = {
		legend: {
			data: ['充值', '提现']
		},
		grid: {
			x: 50,
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
			data: [1022, 20, 50, 30, 10, 80, 100]
		}, {
			name: '提现',
			type: chartType,
			data: [11000, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6]
		}]
	};
	//TODO 通过ajax获取到数据，转化为对应的数组，并赋值给对应的x，y轴，即可完成数据动态加载显示图标；

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