angular.module('onboardApp', ['uiGmapgoogle-maps', 'ngResource'])
  .config(function(uiGmapGoogleMapApiProvider) {
    uiGmapGoogleMapApiProvider.configure({
      key: 'AIzaSyB9le9d1sy7O0n-nDPRCixwhLbi0uzLrR0',
      v: '3.20', //defaults to latest 3.X anyhow
      libraries: 'weather,geometry,visualization'
    });
  })
  .config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  })
  .controller('mapCtrl', ['$scope', '$resource', function($scope, $resource) {
    $scope.map = null;
    $scope.points = [];
    $scope.home = null;
    $scope.work = null;
    $scope.page = 1;

    var states = {
      home: 'home',
      work: 'work',
      none: 'none'
    };

    var PointsService = $resource('/app/profile/api/points', {}, {'query': {method: 'GET', isArray: false}});
    var pointsResponse = PointsService.query(function(){
      $scope.points = _.map(pointsResponse.points, function(p) { p.state = null; return p;} );

    });

    $scope.getMapUrl = function(point) {
      var lat = parseFloat(point.lat),
        lng = parseFloat(point.lng);

      var error = 0.005;

      var polyPoints = [
        [lat+error, lng+error],
        [lat+error, lng-error],
        [lat-error, lng-error],
        [lat-error, lng+error]
      ];

      var polyString = polyline.encode(polyPoints);

      return "https://maps.googleapis.com/maps/api/staticmap?zoom=14&sensor=false&size=400x230&path=weight:0%7Ccolor:orange%7Cfillcolor:0xAA000033%7Cenc:" + polyString + "&markers=icon:http://placehold.it/100x100%7C" + lat + "," + lng;
    };

    $scope.setHome = function(point) {
      point.active = 1;
      point.state = states.home;
      $scope.home = point;

      if ($scope.work == point) {
        $scope.work = null;
      }
    };

    $scope.setWork = function(point) {
      point.active = 1;
      point.state = states.work;
      $scope.work = point;

      if ($scope.home == point) {
        // Unset home
        $scope.home = null;
      }
    };

    $scope.setNone = function(point) {
      if ($scope.home == point)
      {
        $scope.home = null;
      }

      if ($scope.work == point)
      {
        $scope.work = null;
      }

      point.state = states.none;
      point.active = 0;
    };

    $scope.getClass = function(point) {
      var state = point.state;

      if (null === state) {
        return "unset";
      }

      return state;
    };

    $scope.savePoints = function() {
      if ($scope.home === null || $scope.work === null) {
        return;
      }

      // @TODO: Save logic
      $scope.page = 2;
    }
  }])
;

$(function(){

  var getMapUrl = function(point, size, zoom) {
    var lat = parseFloat(point.lat),
      lng = parseFloat(point.lng);

    var error = 0.005;

    var polyPoints = [
      [lat+error, lng+error],
      [lat+error, lng-error],
      [lat-error, lng-error],
      [lat-error, lng+error]
    ];

    var polyString = polyline.encode(polyPoints);

    return "https://maps.googleapis.com/maps/api/staticmap?zoom="+zoom+"&sensor=false&size="+size+"&path=weight:0%7Ccolor:orange%7Cfillcolor:0xAA000033%7Cenc:" + polyString + "&markers=icon:http://placehold.it/100x100%7C" + lat + "," + lng;
  };

  $('.autofill-location').click(function(){
    var $self = $(this),
      lat = $self.data('lat'),
      lng = $self.data('lng'),
      targetLatId = $self.data('target-lat'),
      targetLngId = $self.data('target-lng')
      ;

    var $targetLat = $("#" + targetLatId);
    $targetLat.val(lat);
    $("#" + targetLngId).val(lng);

    $targetLat.closest('form').submit();
  });

  $('.map-image').each(function(){
    var $self = $(this);

    var zoom = $self.data('zoom'),
      size = $self.data('size'),
      markers = $self.data('markers'),
      error = $self.data('error')
      ;

    var imageUrl = getMapUrl({ lat: markers[0], lng: markers[1] }, size, zoom);

    $self.append('<img src="'+imageUrl+'" alt="Google Maps Location of Strava Point">');
  });
});