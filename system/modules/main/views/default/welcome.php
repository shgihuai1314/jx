<?php

use system\core\utils\Tool;

$bundle = \system\modules\main\assets\MainFrameAsset::register($this);
$this->registerJsFile($bundle->baseUrl . '/js/echarts.min.js');
$systemName = Tool::getSystemName();
?>
	<style>
		.admin-main {
			padding: 0;
		}
		
		.page-bgf {
			background-color: transparent;
		}
		
		.bgcolor-f5 {
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
		}
		
		#timeSpan > li {
			float: left;
			width: 100px;
			height: 20px;
			border-radius: 5px;
			background-color: #95BB2F;
			text-align: center;
			cursor: pointer;
			margin-left: 50px;
		}
	</style>
<?php if ($systemName == 'linux'): ?>
	<div class="row layui-clear" style="display:none;">
		<div class="layui-col-lg4 layui-col-md4 layui-layui-col-lgoffset4 layui-col-md-offset4">
			<div class="bgcolor-ff">
				<div class="symbol bgcolor-red">
					<i class="fa fa-calendar-check-o" aria-hidden="true" style="font-size: 8em;"></i>
				</div>
				<div class="value tab-menu">
					<a href="javascript:openUrl('<?= \yii\helpers\Url::toRoute(['/workorder/default/my']) ?>', '我的工单', 'fa fa-tasks');"
							style="color: #ff3850;">
						<h1 style="font-size: 4em;">1</h1>
						<span style="font-size: 2em;">异常信息</span>
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="video-statistics" style="display: none">
		<h3 class="statistics-title">服务器状态</h3>
		<!--圆形图展示在这里-->
		<div class="statistics-ul-block clearfix"></div>
	</div>
	
	<div class="video-statistics" style="display: none">
		<div class="layui-row">
			<div class="layui-col-xs5 internet">
				<div class="layui-col-xs10" id="internet-chart" style="height: 190px;"></div>
				<div class="layui-col-xs10" id="internet-chart-total" style="height: 190px;"></div>
				<!--实时流量-->
			</div>
			<div class="layui-col-xs7 realTime" id="realTime-chart" style="height: 380px;width: 700px;"></div>
		</div>
	</div>
	<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/echarts.min.js"></script>
	<script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<?php else: ?>
	<fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;display: none">
		<legend>欢迎登录系统</legend>
	</fieldset>
	<div class="row layui-clear" style="display:none;">
		
		<div class="layui-col-lg4 layui-col-md4 layui-layui-col-lgoffset4 layui-col-md-offset4">
			<div class="bgcolor-ff">
				<div class="symbol bgcolor-red">
					<i class="fa fa-calendar-check-o" aria-hidden="true" style="font-size: 8em;"></i>
				</div>
				<div class="value tab-menu">
					<a href="javascript:openUrl('<?= \yii\helpers\Url::toRoute(['/workorder/default/my']) ?>', '我的工单', 'fa fa-tasks');"
							style="color: #ff3850;">
						<h1 style="font-size: 4em;">1</h1>
						<span style="font-size: 2em;">异常信息</span>
					</a>
				</div>
			</div>
		</div>
	
	</div>
	<div class="layui-row">
		
		<div class="layui-col-lg3 layui-col-md3 layui-col-sm6 layui-col-xs3 padding-right-20 mb20">
			<div class="bgcolor-f5">
				<div class="symbol bgcolor-green">
					<i class="fa fa-users" aria-hidden="true"></i>
				</div>
				<div class="value tab-menu">
					<a href="javascript:;">
						<h1><?= isset($data['userCount']) ? $data['userCount'] : '-' ?></h1>
						<span>用户数</span>
					</a>
				</div>
			</div>
		</div>
		
		<div class="layui-col-lg3 layui-col-md3 layui-col-sm6 layui-col-xs3 padding-right-20 mb20">
			<div class="bgcolor-f5">
				<div class="symbol bgcolor-yellow">
					<i class="fa fa-user-o" aria-hidden="true"></i>
				</div>
				<div class="value tab-menu">
					<a href="javascript:;">
						<h1><?= isset($data['adminCount']) ? $data['adminCount'] : '-' ?></h1>
						<span>管理员</span>
					</a>
				</div>
			</div>
		</div>
		
		<div class="layui-col-lg3 layui-col-md3 layui-col-sm6 layui-col-xs3 padding-right-20 mb20">
			<div class="bgcolor-f5">
				<div class="symbol bgcolor-blue">
					<i class="fa fa-building" aria-hidden="true"></i>
				</div>
				<div class="value tab-menu">
					<a href="javascript:;">
						<h1><?= isset($data['groupCount']) ? $data['groupCount'] : '-' ?></h1>
						<span>部门</span>
					</a>
				</div>
			</div>
		</div>
		
		<div class="layui-col-lg3 layui-col-md3 layui-col-sm6 layui-col-xs3 padding-right-20 mb20">
			<div class="bgcolor-f5">
				<div class="symbol bgcolor-green">
					<i class="fa fa-id-card" aria-hidden="true"></i>
				</div>
				<div class="value tab-menu">
					<a href="javascript:;">
						<h1><?= isset($data['positionCount']) ? $data['positionCount'] : '-' ?></h1>
						<span>职位</span>
					</a>
				</div>
			</div>
		</div>
	
	</div>
	<!--<div class="layui-row mb20 ">
		<div class="layui-col-lg6 layui-col-md6 layui-col-xs6 yd-notice">
			<div class="bgcolor-ff yd-notice-left">
				<div class="ibox-title">
					<h5>产品信息</h5>
				</div>
				<div class="ibox-content no-padding">
					<table class="layui-table margin0" lay-even lay-skin="nob">
						<colgroup>
							<col width="100">
							<col>
						</colgroup>
						<tbody>
						<tr>
							<td class="text-r bold">系统名称</td>
							<td class="c666"><?/*= Yii::$app->systemConfig->getValue('SYSTEM_NAME') */?></td>
						</tr>
						<tr>
							<td class="text-r bold">系统版本</td>
							<td class="c666 word-break"><?/*= VERSION */?></td>
						</tr>
						<tr>
							<td class="text-r bold">开发者</td>
							<td class="c666"><a href="http://www.yudear.cn"
										target="_blank"><?/*= Yii::$app->systemConfig->getValue('SYSTEM_AUTHOR', '武汉雨滴科技有限公司') */?></a>
							</td>
						</tr>
						<tr>
							<td class="text-r bold">服务器环境</td>
							<td class="c666"><?/*= isset($data['systemName']) ? $data['systemName'] : '-' */?></td>
						</tr>
						<tr>
							<td class="text-r bold">上线时间</td>
							<td class="c666"><?/*= isset($data['onlineAt']) ? $data['onlineAt'] : '-' */?></td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="layui-col-lg6 layui-col-md6 layui-col-xs6 yd-notice">
			<div class="bgcolor-ff yd-notice-right">
				<div class="ibox-title">
					<h5>系统公告</h5>
				</div>
				<div class="ibox-content no-padding">
					<h3 class="bold mb10 mt10">请各位登录用户做好以下几点工作：</h3>
					<p>1. 点击右上角的个人资料页面，查看个人信息是否正确，如果不正确，请务必进行修改，特别是姓名和手机号码，需要随时随地可以联系到的手机号码；</p>
					<p>2. 在个人资料页面中，上传自己的个人头像图片；</p>
					<p>3. 初次登录系统以后请务必修改原始密码，同时建议定期修改个人密码；</p>
					<p>4. 由于权限不同，要管理的内容也不太一样，所以如果出现没有权限访问等字样不用担心；</p>
					<p>5. 如果确实需要某些页面的访问权限，请联系管理员进行授权；</p>
					<p>6. 如果离开电脑，请务必登出账号，以免出现安全隐患；</p>
				</div>
			</div>
		</div>
	</div>-->
<?php endif; ?>
	<script>
        /*产品信息与系统公告高度一致*/
        function heightSame(obj, obj1) {
            var num = obj.height();//左边的高度
            var num1 = obj1.height();//右边的高度
            if (num > num1) {
                obj1.css("height", num + "px");
            } else if (num <= num1) {
                obj.css("height", num1 + "px");
            }
        }

        heightSame($(".yd-notice-left"), $(".yd-notice-right"));
	
	</script>
<?php if ($systemName == 'linux'): ?>
	<script type="text/javascript">
        var OnSpeed = '';//上行流量
        var DownSpeed = '';//下行流量
        var arr = [];//生成环形图
        var bar = '';//网络流量
        var AlwaysSend;//总发送量
        var AlwaysReceive;//总接收量

        //单位转换
        function ForDight(Dight, How) {
            if (Dight < 0) {
                var Last = 0 + "B/s";
            } else if (Dight < 1024) {
                var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "B/s";
            } else if (Dight < 1048576) {
                Dight = Dight / 1024;
                var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "K/s";
            } else {
                Dight = Dight / 1048576;
                var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "M/s";
            }
            return Last;
        }

        $(document).ready(function () {
            getJSONData();//生成环形图
        });

        //生成环形图
       /* function getJSONData() {
            $.ajax({
                url: '',//\yii\helpers\Url::to(['default/system-config'])
                dataType: 'json',
                type: 'get',
                success: function (res) {
                    $('.video-statistics').show();
                    $('.layui-elem-field').show();

                    //$('.statistics-ul-block').html('');
                    $.each(res.data, function (type, data) {
                            if (type == 'pie') {//环形图
                                $.each(data, function (index, item) {
                                    $('.statistics-ul-block').append('<div class="statistics-item">\n' +
                                        '    <div class="pie-title">\n' +
                                        '        <h3>' + item.title + '</h3>\n' +
                                        '    </div>\n' +
                                        '    <div class="pie-chart" id="pie-chart' + index + '"></div>' +
                                        '    <div class="pie-name">' + item.name + '</div>' +
                                        '</div>');

                                    var Option = {
                                        color: item.color,
                                        title: {//title是否显示
                                            show: false,
                                            text: item.title,
                                            top: '3%',
                                            left: '1%',
                                            textStyle: {
                                                color: '#333',
                                                fontStyle: 'normal',
                                                fontWeight: 'normal',
                                                fontFamily: 'sans-serif',
                                                fontSize: 16,
                                            }
                                        },
                                        tooltip: {
                                            trigger: 'axis',
                                            axisPointer: {
                                                type: 'shadow'
                                            }
                                        },
                                        series: [{
                                            name: '占用',
                                            type: 'pie',
                                            radius: ['90%', '100%'],
                                            avoidLabelOverlap: false,
                                            hoverAnimation: true,
                                            label: {
                                                normal: {
                                                    show: false,
                                                    position: 'center',
                                                    textStyle: {
                                                        fontSize: item.fontSize,
                                                        fontWeight: 'bold'
                                                    },
                                                    formatter: '{b}\n{c}%'
                                                },
                                            },
                                            data: [
                                                {
                                                    value: item.value,
                                                    // name: item.name,
                                                    label: {
                                                        normal: {
                                                            show: true,//圆中心默认显示的内容
                                                            color: '#14112d',
                                                            fontWeight: '700',
                                                        },
                                                    }
                                                },
                                                {
                                                    value: 100 - item.value,
                                                    name: ''
                                                }
                                            ]
                                        }]
                                    };

                                    var mychart = echarts.init(document.getElementById('pie-chart' + index));

                                    arr.push(mychart);

                                    Option.series[0].data[0].value = item.value;
                                    mychart.setOption(Option);
                                    echarts.init(document.getElementById('pie-chart' + index)).setOption(Option);
                                })
                            }
                            else if (type == 'bar') {

                                Option = {
                                    tooltip: {
                                        trigger: 'axis',
                                        axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                                            type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                                        }
                                    },
                                    legend: {
                                        left: 0,
                                        top: '5%',
                                        data: ['上行流量', '下行流量']
                                    },
                                    grid: {
                                        left: '1%',
                                        right: '12%',
                                        bottom: '20%',
                                        containLabel: true
                                    },
                                    xAxis: {
                                        name: 'KB/s',
                                        type: 'value',
                                        axisLine: {
                                            lineStyle: {
                                                color: '#808080'
                                            }
                                        },
                                        splitLine: {
                                            lineStyle: {
                                                color: '#eee'
                                            }
                                        },
                                    },
                                    yAxis: {
                                        type: 'category',
                                        // show:false,
                                        data: ['实时流量'],
                                        axisLine: {
                                            lineStyle: {
                                                color: '#808080'
                                            }
                                        }
                                    },
                                    series: [
                                        {
                                            name: '上行流量',
                                            type: 'bar',
                                            barWidth: '20',
                                            stack: '总量',
                                            label: {
                                                normal: {
                                                    show: true,

                                                }
                                            },
                                            data: [],
                                            itemStyle: {
                                                normal: {
                                                    color: '#f7b851',
                                                    barBorderRadius: [10, 0, 0, 10],
                                                },
                                            }
                                        },
                                        {
                                            name: '下行流量',
                                            type: 'bar',
                                            barWidth: '20',
                                            stack: '总量',
                                            label: {
                                                normal: {
                                                    show: true,

                                                }
                                            },
                                            data: [],
                                            itemStyle: {
                                                normal: {
                                                    color: '#52a9ff',
                                                    barBorderRadius: [0, 10, 10, 0],
                                                },
                                            }
                                        },
                                    ]
                                };

                                //初始化
                                Option.series[0].data[0] = res.data.speed.NetOutSpeed;
                                Option.series[1].data[0] = res.data.speed.NetInputSpeed;

                                var mychart_bar = echarts.init(document.getElementById('internet-chart'));

                                mychart_bar.setOption(Option);

                                bar = mychart_bar;

                            }
                            else if (type == 'totalBar') {
                                TotalOption = {
                                    tooltip: {
                                        trigger: 'axis',
                                        axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                                            type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                                        }
                                    },
                                    legend: {
                                        left: 0,
                                        top: '10%',
                                        data: ['总接收量', '总发送量']
                                    },
                                    grid: {
                                        left: '1%',
                                        right: '12%',
                                        bottom: '10%',
                                        containLabel: true
                                    },
                                    xAxis: {
                                        name: 'GB',
                                        type: 'value',
                                        axisLine: {
                                            lineStyle: {
                                                color: '#808080'
                                            }
                                        },
                                        splitLine: {
                                            lineStyle: {
                                                color: '#eee'
                                            }
                                        }
                                    },
                                    yAxis: {
                                        type: 'category',
                                        axisLine: {
                                            lineStyle: {
                                                color: '#808080'
                                            }
                                        },
                                        data: ['网络流量'],
                                    },
                                    series: [
                                        {
                                            name: '总接收量',
                                            type: 'bar',
                                            barWidth: '20',
                                            stack: '总量',
                                            label: {
                                                normal: {
                                                    show: true,
                                                }
                                            },
                                            data: [],
                                            itemStyle: {
                                                normal: {
                                                    color: '#00ca5b',
                                                    barBorderRadius: [10, 0, 0, 10],
                                                },
                                            }
                                        },
                                        {
                                            name: '总发送量',
                                            type: 'bar',
                                            barWidth: '20',
                                            stack: '总量',
                                            label: {
                                                normal: {
                                                    show: true,

                                                }
                                            },
                                            data: [],
                                            itemStyle: {
                                                normal: {
                                                    color: '#ff226b',
                                                    barBorderRadius: [0, 10, 10, 0],
                                                },
                                            }
                                        },
                                    ]
                                };
                                //初始化
                                TotalOption.series[0].data[0] = res.data.speed.AlwaysReceive;
                                TotalOption.series[1].data[0] = res.data.speed.AlwaysSend;

                                var mychart_totalBar = echarts.init(document.getElementById('internet-chart-total'));
                                mychart_totalBar.setOption(TotalOption);
                                totalBar = mychart_totalBar;
                            }
                        }
                    );
                },
            });
        }*/

        //给图形替换数据
     /*   setInterval(function () {
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            $.ajax({
                url: '',
                type: "post",
                dataType: 'json',
                data: {"upside": OnSpeed, "downside": DownSpeed, "_csrf-system": csrfToken},
                success: function (res) {
                    $.each(res.data, function (type, data) {
                        if (type == 'pie') {//环形图
                            $.each(data, function (index, item) {
                                var Option = {
                                    color: item.color,
                                    title: {//title是否显示
                                        show: false,
                                        text: item.title,
                                        top: '3%',
                                        left: '1%',
                                        textStyle: {
                                            color: '#333',
                                            fontStyle: 'normal',
                                            fontWeight: 'normal',
                                            fontFamily: 'sans-serif',
                                            fontSize: 16,
                                        }
                                    },
                                    tooltip: {
                                        trigger: 'axis',
                                        axisPointer: {
                                            type: 'shadow'
                                        }
                                    },
                                    series: [{
                                        name: '占用',
                                        type: 'pie',
                                        radius: ['90%', '100%'],
                                        avoidLabelOverlap: false,
                                        hoverAnimation: true,
                                        label: {
                                            normal: {
                                                show: false,
                                                position: 'center',
                                                textStyle: {
                                                    fontSize: item.fontSize,
                                                    fontWeight: 'bold'
                                                },
                                                formatter: '{b}\n{c}%'
                                            },
                                        },
                                        data: [{
                                            value: item.value,
                                            // name: item.name,
                                            label: {
                                                normal: {
                                                    show: true,//圆中心默认显示的内容
                                                    color: '#14112d',
                                                    fontWeight: '700',
                                                },
                                            }
                                        },
                                            {
                                                value: 100 - item.value,
                                                name: ''
                                            }
                                        ]
                                    }]
                                };
                                //环形图
                                Option.series[0].data[0].value = item.value;
                                myChart = arr[index];
                                myChart.setOption(Option);
                            })
                        }
                        else if (type == 'bar') {
                            OnSpeed = res.data.speed.NetOutSpeed;//上行流量
                            DownSpeed = res.data.speed.NetInputSpeed;//下行流量

                            //网络流量
                            OnSpeed = Math.floor(OnSpeed * 100) / 100;
                            DownSpeed = Math.floor(DownSpeed * 100) / 100;
                            //赋值
                            Option.series[0].data[0] = OnSpeed;
                            Option.series[1].data[0] = DownSpeed;

                            bar.setOption(Option);

                        } else if (type == 'totalBar') {
                            AlwaysReceive = res.data.speed.AlwaysReceive;//总发送量
                            AlwaysSend = res.data.speed.AlwaysSend;//总接收量

                            //网络流量
                            Send = Math.floor(AlwaysSend * 100) / 100;
                            Receive = Math.floor(AlwaysReceive * 100) / 100;
                            //赋值
                            TotalOption.series[0].data[0] = Receive;
                            TotalOption.series[1].data[0] = Send;

                            totalBar.setOption(TotalOption);
                        }
                    });
                },
            });
        }, 3000);*/

        //网络实时流量
        function NetImg() {
            var myChartNetwork = echarts.init(document.getElementById('realTime-chart'));
            //x坐标轴默认的时间
            var time = (new Date()).getTime();
            var xData = [format(time - (8 * 3 * 1000)), format(time - (7 * 3 * 1000)), format(time - (6 * 3 * 1000)), format(time - (5 * 3 * 1000)), format(time - (4 * 3 * 1000)), format(time - (3 * 3 * 1000)), format(time - (2 * 3 * 1000)), format(time - (3 * 1000)), format(time)];
            var yData = ['', '', '', '', '', '', '', '', OnSpeed];
            var zData = ['', '', '', '', '', '', '', '', DownSpeed];

            function getTime() {
                var now = new Date();
                var hour = now.getHours();
                var minute = now.getMinutes();
                var second = now.getSeconds();
                if (minute < 10) {
                    minute = "0" + minute;
                }
                if (second < 10) {
                    second = "0" + second;
                }
                var nowdate = hour + ":" + minute + ":" + second;
                return nowdate;
            }

            function ts(m) {
                return m < 10 ? '0' + m : m
            }

            function format(sjc) {
                var time = new Date(sjc);
                var h = time.getHours();
                var mm = time.getMinutes();
                var s = time.getSeconds();
                return ts(h) + ':' + ts(mm) + ':' + ts(s);
            }


            // 指定图表的配置项和数据
            var Option = {
                title: {
                    text: '实时数据更新',
                    textStyle: {
                        color: '#333',
                        fontSize: 20,
                    },
                },
                grid: {
                    top: '25%',
                    left: '5%',
                    right: '10%',
                    bottom: '3%',
                    containLabel: true
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['上行', '下行'],
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: xData,
                    axisLine: {
                        lineStyle: {
                            color: "#666"
                        }
                    }
                },
                yAxis: {
                    name: 'KB/s',
                    splitLine: {
                        lineStyle: {
                            color: "#eee"
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            color: "#666"
                        }
                    }
                },
                series: [{
                    name: '上行',
                    type: 'line',
                    data: yData,
                    smooth: true,
                    showSymbol: false,
                    symbol: 'circle',
                    symbolSize: 6,
                    areaStyle: {
                        normal: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                offset: 0,
                                color: 'rgba(255, 140, 0,0.5)'
                            }, {
                                offset: 1,
                                color: 'rgba(255, 140, 0,0.8)'
                            }], false)
                        }
                    },
                    itemStyle: {
                        normal: {
                            color: '#f7b851'
                        }
                    },
                    lineStyle: {
                        normal: {
                            width: 1
                        }
                    }
                }, {
                    name: '下行',
                    type: 'line',
                    data: zData,
                    smooth: true,
                    showSymbol: false,
                    symbol: 'circle',
                    symbolSize: 6,
                    areaStyle: {
                        normal: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                offset: 0,
                                color: 'rgba(30, 144, 255,0.5)'
                            }, {
                                offset: 1,
                                color: 'rgba(30, 144, 255,0.8)'
                            }], false)
                        }
                    },
                    itemStyle: {
                        normal: {
                            color: '#52a9ff'
                        }
                    },
                    lineStyle: {
                        normal: {
                            width: 1
                        }
                    }
                }]
            };

            setInterval(function () {
                //给数轴赋值
                var time = (new Date()).getTime();
                xData.push(format(time - (3 * 1000)));

                yData.push(OnSpeed);
                zData.push(DownSpeed);

                if (yData.length >= 9 && zData.length >= 9) {
                    yData.shift();
                    zData.shift();
                    xData.shift();
                } else {
                    xData = [format(time - (8 * 3 * 1000)), format(time - (7 * 3 * 1000)), format(time - (6 * 3 * 1000)), format(time - (5 * 3 * 1000)), format(time - (4 * 3 * 1000)), format(time - (3 * 3 * 1000)), format(time - (2 * 3 * 1000)), format(time - (3 * 1000)), format(time)];
                    zData = ['', '', '', '', '', '', '', '', OnSpeed];
                    yData = ['', '', '', '', '', '', '', '', DownSpeed];
                }

                myChartNetwork.setOption({
                    xAxis: {
                        data: xData
                        //data: ["14:53:14","14:53:17","14:53:20","14:53:23", "14:53:26", "14:53:29", "14:53:32","14:53:35","14:53:38"]
                    },
                    series: [{
                        data: yData
                        //data: [63.56, 63.7, 63.57, 63.83, 63.65, 63.75, 63.58, 74.32, 86.59]
                    }, {
                        data: zData,
                        //data:[64.02,63.74,64.04,63.82,64.15,63.75,64.05,74.33,70.26]
                    }]
                });
            }, 3000);
            // 使用刚指定的配置项和数据显示图表。

            myChartNetwork.setOption(Option);
            window.addEventListener("resize", function () {
                myChartNetwork.resize();
            });
        }

  /*      $.ajax({
            url: '',
            dataType: 'json',
            type: 'get',
            success: function (res) {
                OnSpeed = res.data.speed.NetInputSpeed;
                DownSpeed = res.data.speed.NetOutSpeed;
                NetImg();
            },
        });*/

	</script>
<?php endif; ?>