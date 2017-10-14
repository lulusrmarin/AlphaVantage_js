<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Alpha Vantage Search</title>

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
</head>
<body>
	<div ng-app="stockSearch" ng-controller="stockCtrl" style="padding: 16px;">
        <div id="controls">
            <input type="text" placeholder="Symbol" ng-model="symbol">
            <br>
            <select ng-model="func" ng-options="option.value as option.name for option in function_data" ng-change="loadOptions()"></select>
        </div>
        <div ng-repeat="(name, option) in options">
            <select ng-model="opts[ options[ name ] ]" ng-options="option.value as option.name for option in option"></select><br>
        </div>
        <div ng-repeat="(key, value) in inputs">
            <input type="text" ng-attr-placeholder="{{key}}"><br>
        </div>
        <i class="fa fa-refresh" aria-hidden="true" ng-click="change()" style="cursor: pointer;"></i>
        <br><br>
	    <table class="w3-table-all w3-tiny w3-striped">
            <thead>
                <tr>
                    <th ng-attr-colspan="{{colspan}}" class="w3-green">
                        <span ng-if="symbol">Results for: {{symbol.toUpperCase()}}</span>
                        <span ng-if="symbol && func"> - </span>
                        <span ng-if="func">Using Series: {{func}}</span>
                        <span ng-if="func && intrvl"> - </span>
                        <span ng-if="intrvl">Interval: {{intrvl}}</span>
                    </th>
                </tr>
                <tr>
                    <th ng-show="data" class="w3-blue w3-border-right">Date</th>
                    <th class="w3-blue w3-border-right" ng-repeat="(key, value) in firstRow">{{key}}</th>
                </tr>
            </thead>
            <tbody ng-repeat="(key, value) in data">
                <tr class="w3-hover-gray">
                    <td class="w3-border-right">{{key}}</td>
                    <td class="w3-border-right" ng-repeat="(key2, value2) in firstRow">{{value[key2]}}</td>
                </tr>
            </tbody>
	    </table>
	</div>
</body>

<script>
    // API Data
    var api_key = 1953;
    var api_data = {};
    var options = {};
    var inputs = {};

    options['interval_data'] = {};
    options['series_data'] = {};

    options['fastkperiod'] = {};
    options['slowkperiod'] = {};
    options['slowdperiod'] = {};
    options['fastmatypes'] = {};
    options['slowmatypes'] = {};
    options['signalmatypes'] = {};
    options['slowkmatypes'] = {};
    options['slowdmatypes'] = {};

    var base_url = 'https://www.alphavantage.co/query';

    api_data['functions'] = [
        { name: 'Intraday', value: 'TIME_SERIES_INTRADAY' },
        { name: 'Daily', value: 'TIME_SERIES_DAILY' },
        { name: 'Daily (Adj. Close)', value: 'TIME_SERIES_DAILY_ADJUSTED' },
        { name: 'Weekly', value: 'TIME_SERIES_WEEKLY' },
        { name: 'Monthly', value: 'TIME_SERIES_MONTHLY' },
        { name: 'Simple Moving Average', value: 'SMA' },
        { name: 'Exponential Moving Average', value: 'EMA' },
        { name: 'Weighted Moving Average', value: 'WMA' },
        { name: 'Double Exponential Moving Avg', value: 'DEMA' },
        { name: 'Triple Exponential Moving Avg', value: 'TEMA' },
        { name: 'Triangular Moving Average', value: 'TRIMA' },
        { name: 'Kaufman Adaptive Moving Avg', value: 'KAMA' },
        { name: 'MESA Adaptive Moving Avg', value: 'MAMA' },
        { name: 'T3 Triple Exponential Moving Avg', value: 'T3' },
        { name: 'Moving Avg Convergence / Divergence', value: 'MACD' },
        { name: 'Moving Avg Convergence / Divergence EXT', value: 'MACDEXT' },
        { name: 'Stochastic Oscillator', value: 'STOCH' }
    ];

    options['interval_data']['TIME_SERIES_INTRADAY'] = [
        { name: '-- Interval --', value: '0min' },
        { name: '1 Minute', value: '1min' },
        { name: '5 Minutes', value: '5min' },
        { name: '15 Minutes', value: '15min' },
        { name: '30 Minutes', value: '30min' },
        { name: '1 Hour', value: '60min' }
    ];

    options['interval_data']['SMA'] = options['interval_data']['EMA'] = options['interval_data']['WMA'] = options['interval_data']['DEMA'] = options['interval_data']['TEMA'] =
    options['interval_data']['TRIMA'] = options['interval_data']['KAMA'] = options['interval_data']['MAMA'] = options['interval_data']['T3'] =
    options['interval_data']['MACD'] = options['interval_data']['MACDEXT'] = options['interval_data']['STOCH'] = [
        { name: '-- Interval --', value: '0min' },
        { name: '1 Minute', value: '1min' },
        { name: '5 Minutes', value: '5min' },
        { name: '15 Minutes', value: '15min' },
        { name: '30 Minutes', value: '30min' },
        { name: 'Daily', value: 'daily' },
        { name: 'Weekly', value: 'weekly' },
        { name: 'Monthly', value: 'monthly' }
    ];

    options['series_data']['SMA'] = options['series_data']['EMA'] = options['series_data']['WMA'] = options['series_data']['DEMA'] = options['series_data']['TEMA'] =
    options['series_data']['TRIMA'] = options['series_data']['KAMA'] = options['series_data']['MAMA'] = options['series_data']['T3'] =
    options['series_data']['MACD'] = options['series_data']['MACDEXT'] = [
            { name: '-- Series --', value: '0min' },
            { name: 'Close', value: 'close' },
            { name: 'Open', value: 'open' },
            { name: 'High', value: 'high' },
            { name: 'Low', value: 'low' }
    ];

    options['fastmatypes']['MACDEXT'] = options['slowmatypes']['MACDEXT'] = options['signalmatypes']['MACDEXT'] =
    options['slowkmatypes']['STOCH'] = options['slowdmatypes']['STOCH'] = [
        { name: 'SMA', value: 0 },
        { name: 'EMA', value: 1 },
        { name: 'WMA', value: 2 },
        { name: 'DEMA', value: 3 },
        { name: 'TEMA', value: 4 },
        { name: 'TRIMA', value: 5 },
        { name: 'KAMA', value: 6 },
        { name: 'MESA', value: 7 },
        { name: 'MAMA', value: 8 },
    ];

    inputs['periods'] = ['SMA', 'EMA', 'WMA', 'DEMA', 'TEMA', 'TRIMA', 'KAMA', 'MAMA', 'T3'];
    inputs['fastPeriods'] = inputs['slowPeriods'] = inputs['signalPeriods'] = [ 'MACD', 'MACDEXT' ];
    inputs['fastkperiod'] = inputs['slowkperiod'] = inputs['slowdperiod'] = ['STOCH'];
    // End API Data

    // Declare our overall app
    var app = angular.module('stockSearch', []);

    // Where the magic happens
    app.controller( 'stockCtrl', function( $scope, $http ) {
        // Seed the functions dropdown
        $scope.function_data = api_data['functions'];
        // Set function default
        $scope.func = $scope.function_data[0].value;

        // Setup controls
        $scope.loadOptions = function() {
            $scope[ 'options' ] = {};
            $scope[ 'inputs' ] = {};

            for ( key in options )
                for( key2 in options[key] )
                    if ( key2 == $scope.func )
                        $scope[ 'options' ][ key ] = options[ key ][ key2 ];

            for ( key in inputs )
                if ( inputs[ key ].includes( $scope.func ) )
                    $scope[ 'inputs' ][ key ] = true;
        };

        // Declare the change function for use in the scope
        $scope.change = function() {
            console.log($scope);

            $scope.firstRow = null;

            url = base_url
                + '?function=' + $scope.func + '&symbol=' + $scope.symbol
                + ( $scope.intrvl ? '&interval=' + $scope.intrvl : '' )
                + ( $scope.timePeriod ? '&time_period=' + $scope.timePeriod : '' )
                + ( $scope.seriesType ? '&series_type=' + $scope.timePeriod : '' )
                + ( $scope.fastPeriod ? '&fastperiod=' + $scope.timePeriod : '' )
                + ( $scope.slowPeriod ? '&slowperiod=' + $scope.timePeriod : '' )
                + ( $scope.signalPeriod ? '&signalperiod=' + $scope.timePeriod : '' )
                + '&apikey=' + api_key;

            // An AJAX request to a constructed API URL
	        $http.get( url )
            .then( function( response ) {
                //console.log( url );
                //console.log( response );

                if( response.data ) {
                    idx = Object.keys(response.data)[1];
                    $scope.data = response.data[ idx ];
                }

                if( $scope.data ) {
                    row1 = Object.keys($scope.data)[0];
                    $scope.firstRow = $scope.data[ row1 ];
                    $scope.colspan = row1.length;
                }
            },
            // What to do during an error
            function( response ) {
                console.log( response );
            });
	    }
    });

</script>
</html>