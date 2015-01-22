var MiningCtrls = angular.module('MiningCtrls', []);

function MiningLog(options) {
    this.title = options.title;
    this.data = null;
    this.lines = [];
    this.finished = false;
    
    this.finish = function() {
        this.finished = true;
    }
    this.errors = [];
    
}
MiningCtrls.controller('MiningCtrl', function($scope, $rootScope, $http,
    $stateParams, Mining) {
    $scope.part = '';
    if (!!$stateParams.part) {
        $scope.part = $stateParams.part;
    }

    $scope.global = {
        logs : [],
        errors : [],
        categories : [],
        cacheCategories : [],
        category : {}
    };
    
    var req = {
        method : 'GET',
        url : './api.php?round=getAllProducts'
    }

    $scope.state = {
    // gettingChapter : false
    };

    $scope.done = false;

    $scope.global.logs.push(new MiningLog({
        "title" : "Getting all product index files"
    }));
    $http(req).success(function(res) {
        var log = $scope.global.logs[0];
        log.title = 'Getting list';
        log.data = res.items;
        log.lines.push({content: res.length + " products found"});
        log.finish();

        // Processing round 2
        // New log
        log = new MiningLog({
            "title" : "Importing products"
        });
        
        // save cache for later use
        $scope.global.cacheProducts = angular.copy(res);
//        
        $scope.global.products = res;
//
        $scope.global.logs.push(log);
        $scope.global.round2_remaining = $scope.global.products.length;
//        
//        // Open 5 threads
        $scope.importProduct(log);
        $scope.importProduct(log);
        $scope.importProduct(log);
        $scope.importProduct(log);
        $scope.importProduct(log);

    }).error(function(xhr) {
        console.log(xhr);
    });

    $scope.importProduct = function(log, callback) {
        if ($scope.global.products.length > 0) {
            
            var product = $scope.global.products[0];
            $scope.global.products.shift();
            
            var req = {
                method : 'GET',
                url : './api.php?round=importProduct&md5=' + product.md5
            }
            
            var line = {content: 'Importing ' + product.name, done : false};
            log.lines.push(line);
            $http(req).success(function(res) {
                $scope.global.round2_remaining--;
                
                // continue crawling
                line.content = 'Product: ' + product.name + ": IMPORTED";
                line.done = true;
                
                if ($scope.global.round2_remaining == 0) {
                	log.finish();
                    
                    // processing parse states
                    $scope.global.products = angular.copy($scope.global.cacheProducts);
                    
                    //$scope.prepareProductsListing();
                    
                } else {
                    $scope.importProduct(log);
                }
            }).error(function(xhr) {
                console.log(xhr);
                log.errors.push("Error importing " + product.name);
                
                $scope.importProduct(log);
            });
        } else {
            //stateLog.finish();
            
            // crawling cities of states
        }
    }
});