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
			data: [2,2,2,2,2,2,2]//x轴
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
			data: [10,11,12,23,52,65,25]//y轴
		}]
	};


// 异步加载数据
$.get("/index.php?s=/Home/Financial/financialThisWeekReturn").done(function (data) {
 // 填入数据
 lineChart.setOption({
     xAxis: [{
         type: 'category',
         data: data.categories
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
         data:data.data
     }]
 });
});
var lineChart = echarts.init(byId('lineChart'));
lineChart.setOption(chartOption);

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
        data: [2,2,2,2,2,2,2]//x轴
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
        data: [10,11,12,23,52,65,25]//y轴
    }]
	};
//上周充值
$.get("/index.php?s=/Home/Financial/financialLastWeekReturn").done(function (data) {
    // 填入数据
    lineChart2.setOption({
        xAxis: [{
            type: 'category',
            data: data.categories
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
            data:data.data
        }]
    });
});
var lineChart2 = echarts.init(byId('lineChart2'));
lineChart2.setOption(chartOption2);
