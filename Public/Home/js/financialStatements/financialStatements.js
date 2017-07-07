



var byId = function(id){
	return document.getElementById(id);
};
//本周
var chartOption = {
		legend: {
			data: ['充值']
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
			data: [7,6,5,4,3,2,1]//x轴
		}],
		yAxis: [{
			type: 'value',
			splitArea: {
				show: true
			}
		}],
		series: [{
			name: '充值',
			type: "line",
			data: [9,5,3,8,6,1,7]//y轴
		}]
	};

var lineChart = echarts.init(byId('lineChart'));
lineChart.setOption(chartOption);
// 异步加载数据
//$.get('data.json').done(function (data) {
//  // 填入数据
//  lineChart.setOption({
//      xAxis: {
//          data: data.categories
//      },
//      series: [{
//          // 根据名字对应到相应的系列
//          name: '充值',
//          data: data.data
//      }]
//  });
//});


//上周
var chartOption2 = {
		legend: {
			data: ['充值']
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
			data: [4,4,4,4,4,4,4]
		}],
		yAxis: [{
			type: 'value',
			splitArea: {
				show: true
			}
		}],
		series: [{
			name: '充值',
			type: "line",
			data: [222, 20, 50, 30, 10, 80, 1000]
		}]
	};
var lineChart2 = echarts.init(byId('lineChart2'));
lineChart2.setOption(chartOption2);
//$.get('data.json').done(function (data) {
//  // 填入数据
//  lineChart.setOption({
//      xAxis: {
//          data: data.categories
//      },
//      series: [{
//          // 根据名字对应到相应的系列
//          name: '充值',
//          data: data.data
//      }]
//  });
//});

