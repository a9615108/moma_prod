define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent'
], function ($, _, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'report/chart',

            provider: '${ $.provider }:data',

            imports: {
                comparison:      '${ $.provider }:data.comparison',
                rows:            '${ $.provider }:data.items',
                dimensionColumn: '${ $.provider }:data.dimensionColumn',
                columnsProvider: '${ $.columnsProvider }:elems',
                params:          '${ $.provider }:params'
            },

            exports: {},

            listens: {
                rows:            'initChart',
                dimensionColumn: 'onChangeDimensionColumn',
                chartType:       'initChart',
                columnsProvider: 'prepareColumns'
            },

            wrapSelector: '.report__chart-wrap'
        },

        initialize: function () {
            var self = this;

            this._super();

            _.bindAll(this, 'toggleColumn', 'setChartType', 'prepareColumns', 'initChart');

            self.types = ['bar', 'line'];

            if (this.chartType() == "geo") {
                self.types.push('geo');
            }

            if (!$.isArray(this.vAxis)) {
                this.vAxis = [this.vAxis];
            }

            var throttledInit = _.throttle(this.initChart, 100);
            $(window).on('resize', function () {
                throttledInit();
            }.bind(this));

            return this;
        },

        initObservable: function () {
            this._super()
                .observe({
                    rows:           [],
                    columns:        [],
                    chartType:      this.chartType == 'column' ? "bar" : this.chartType,
                    visibleColumns: []
                });

            return this;
        },

        initChart: function () {
            if (this.chartType() == "bar" || this.chartType() == "line") {
                this.initColumnChart();
            } else if (this.chartType() == "geo") {
                this.initGeoChart();
            }
        },

        initColumnChart: function () {
            if (!document.getElementById('chart_div')) {
                return;
            }

            var self = this;

            var data = this.getData();

            if (!data) {
                $(this.wrapSelector).hide();
                return;
            }

            $(this.wrapSelector).show();

            data = google.visualization.arrayToDataTable(data);

            if (!this.chart) {
                if (this.chartType() == 'bar') {
                    this.chart = new google.charts.Bar(document.getElementById('chart_div'));
                } else if (this.chartType() == 'line') {
                    this.chart = new google.charts.Line(document.getElementById('chart_div'));
                } else {
                    this.chart = new google.charts.Bar(document.getElementById('chart_div'));
                }
            }

            var options = {
                legend:   {position: 'none'},
                colors:   this.getColors(),
                axes:     {
                    x: {0: {'label': ''}},
                    y: {
                        all: {
                            format: {
                                pattern: 'decimal'
                            }
                        }
                    }
                },
                width:    $('.report__chart-wrap').width() * 0.80,
                height:   400,
                fontName: "'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif"
            };

            var view = new google.visualization.DataView(data);

            this.chart.draw(view, options);
        },

        initPieChart: function () {
            var self = this;

            var chart = new google.charts.PieChart(document.getElementById('chart_div'));

            var data = this.getData();

            if (!data) {
                $('#chart_div').hide();
            } else {
                $('#chart_div').show();
            }

            var options = {
                legend:   {position: 'none'},
                colors:   self.getColors(),
                axes:     {
                    x: {
                        0: {'label': ''}
                    }
                },
                width:    '100%',
                height:   400,
                fontName: "'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif"
            };

            var view = new google.visualization.DataView(data);

            chart.draw(view, options);
        },

        initGeoChart: function () {
            if (!document.getElementById('chart_div')) {
                return;
            }

            var self = this;

            var data = this.getData();

            if (!data) {
                $(this.wrapSelector).hide();
                return;
            }

            $(this.wrapSelector).show();

            data = google.visualization.arrayToDataTable(data);

            if (!this.chart) {
                this.chart = new google.visualization.GeoChart(document.getElementById('chart_div'));
            }

            var options = {
                legend:   {position: 'none'},
                colors:   this.getColors(),
                axes:     {
                    x: {0: {'label': ''}}
                },
                width:    '100%',
                height:   400,
                fontName: "'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif"
            };

            if (this.params.filters && this.params.filters['sales_order_address|country']) {
                options['region'] = this.params.filters['sales_order_address|country'];
                options['resolution'] = 'provinces';
            }

            var view = new google.visualization.DataView(data);

            this.chart.draw(view, options);
        },

        getData: function () {
            var rows = [];

            var header = [];
            _.each(this.columns.filter('isDimension'), function (column) {
                header.push(column.label);
            });

            _.each(this.columns.filter('visibleOnChart'), function (column) {
                header.push(column.label);

                if (this.comparison) {
                    header.push(column.label);
                }
            }, this);

            rows.push(header);

            if (header.length < 2) {
                return false;
            }

            _.each(this.rows(), function (obj) {
                var row = [];

                _.each(this.columns.filter('isDimension'), function (column) {
                    row.push(this.getCellValue(column, obj) + "");
                }, this);

                _.each(this.columns.filter('visibleOnChart'), function (column) {
                    row.push(this.getCellValue(column, obj));

                    if (this.comparison) {
                        row.push(this.getCellValue(column, obj, 'c_'));
                    }
                }, this);

                rows.push(row);
            }, this);

            if (rows.length < 2) {
                return false;
            }

            return rows;
        },

        getCellValue: function (column, row, prefix) {
            var index = column.index;

            if (prefix !== undefined) {
                index = prefix + index;
            }

            var value = row[index];

            var type = column.valueType;
            if (type == 'number' || type == 'price') {
                value = parseFloat(parseFloat(value).toFixed(2));
            } else if (type == 'date') {
                value = new Date(Date.parse(value));
            } else if (column.componentType == "country") {
            } else {
                value = column.getLabel(row);
            }

            return value;
        },

        getColors: function () {
            var colors = [];

            _.each(this.columns.filter('visibleOnChart'), function (column) {
                colors.push(column.color);
            }, this);

            return colors;
        },

        toggleColumn: function (column) {
            column.visibleOnChart(!column.visibleOnChart());

            this.initChart();
        },

        setChartType: function (type) {
            this.chart = null;
            this.chartType(type);
        },

        onChangeDimensionColumn: function () {
            this.prepareColumns();
            this.initChart();
        },

        prepareColumns: function () {
            this.columns([]);

            _.each(this.columnsProvider, function (column) {
                var isVisible = _.indexOf(this.vAxis, column.index) >= 0 && column.index != this.dimensionColumn;

                column.visibleOnChart = ko.observable(isVisible);

                column.isDimension = column.index == this.dimensionColumn;

                column.isInternal = column.isDimension || column.index == 'actions';

                this.columns.push(column);
            }, this);
        }
    });
});
