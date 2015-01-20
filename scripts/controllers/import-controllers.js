var MiningCtrls = angular.module('MiningCtrls', []);

function MiningLog(options) {
    this.title = options.title;
    this.data = null;
    this.lines = [];
    this.finished = false;
    
    this.finish = function() {
        this.finished = true;
    }
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
        url : './api.php?round=getCategories'
    }

    $scope.state = {
    // gettingChapter : false
    };

    $scope.done = false;

    $scope.global.logs.push(new MiningLog({
        "title" : "Getting list of categories"
    }));
    $http(req).success(function(res) {
        var log = $scope.global.logs[0];
        log.title = 'List of categories';
        log.data = res.items;
        log.lines.push({content: res.length + " categories found"});
        log.finish();

        // Processing round 2
        // New log
        log = new MiningLog({
            "title" : "Indexing products of each category ..."
        });
        
        // save cache for later use
        $scope.global.cacheCategories = angular.copy(res);
//        
        $scope.global.categories = res;
//
        $scope.global.logs.push(log);
        $scope.global.round2_remaining = $scope.global.categories.length;
//        
//        // Open 5 threads
        $scope.indexProducts(log);
        $scope.indexProducts(log);
        $scope.indexProducts(log);
        $scope.indexProducts(log);
        $scope.indexProducts(log);

    }).error(function(xhr) {
        console.log(xhr);
    });

    $scope.indexProducts = function(log, callback) {
        if ($scope.global.categories.length > 0) {
            
            var category = $scope.global.categories[0];
            $scope.global.categories.shift();
            
            var req = {
                method : 'GET',
                url : './api.php?round=getProducts&menu=' + category.menu.name + '&subMenu='+category.subMenu.name 
                + '&subMenu_lv1='+category.subMenu_lv1.name + '&url=' + encodeURIComponent(category.subMenu_lv1.href)
            }
            
            var line = {content: category.subMenu_lv1.name + ': indexing ...', done : false};
            log.lines.push(line);
            $http(req).success(function(res) {
                $scope.global.round2_remaining--;
                
                // continue crawling
                line.content = category.subMenu_lv1.name + ": INDEXED";
                line.done = true;
                
                if ($scope.global.round2_remaining == 0) {
                	log.finish();
                    
                    // processing parse states
                    $scope.global.categories = angular.copy($scope.global.cacheCategories);
                    $scope.prepareProductsListing();
                    
                } else {
                    $scope.indexProducts(log);
                }
            }).error(function(xhr) {
                console.log(xhr);
                log.errors.push("Error crawling " + category.subMenu_lv1.name);
            });
        } else {
            //stateLog.finish();
            
            // crawling cities of states
        }
    }

    $scope.prepareProductsListing = function() {
        if ($scope.global.categories.length > 0) {
            
            var category = $scope.global.categories[0];
            $scope.global.categories.shift();
            
            var log = new MiningLog({
                "title" : "Crawling products of " + category.subMenu_lv1.name
            });
            $scope.global.logs.push(log);
            
            var line = {content: 'Getting product index', done : false};
            log.lines.push(line);
            
            var req = {
                method : 'GET',
                url : './api.php?round=importProduct&menu=' + category.menu.name + '&subMenu='+category.subMenu.name 
                + '&subMenu_lv1='+category.subMenu_lv1.name + '&url=' + encodeURIComponent(category.subMenu_lv1.href)
            }
            $http(req).success(function(res) {
                
                // continue crawling
                line.content = 'Getting product index : DONE';
                line.done = true;
                
                // assign to global variable
                
                $scope.global.category[category.subMenu_lv1.name] = {
                    products : res,
                    name : category.subMenu_lv1.name
                };
                
                $scope.global.products_remaining = res.length;
                
                // 5x running
                $scope.getProduct(log, category, category.subMenu_lv1.name);
                $scope.getProduct(log, category, category.subMenu_lv1.name);
                $scope.getProduct(log, category, category.subMenu_lv1.name);
                $scope.getProduct(log, category, category.subMenu_lv1.name);
                $scope.getProduct(log, category, category.subMenu_lv1.name);
                
            }).error(function(xhr) {
                console.log(xhr);
                log.errors.push("Error preparing info for " + category.subMenu_lv1.name);
            });
        } else {
            //stateLog.finish();
            
            // crawling cities of states
        }
    }
    
    $scope.getProduct = function(log, info, categoryIndex) {
        if ($scope.global.category[categoryIndex].products.length > 0) {
            var category = $scope.global.category[categoryIndex];
            
            var product = category.products[0];
            
            category.products.shift();
            
            var line = {content: 'Getting product information of ' + product.name, done : false};
            log.lines.push(line);
            
            var req = {
                method : 'GET',
                url : './api.php?round=getProduct&menu=' + info.menu.name + '&subMenu='+info.subMenu.name 
                + '&subMenu_lv1='+info.subMenu_lv1.name + '&productName='+ product.name + '&url=' + encodeURIComponent(product.href)
            }
            
            $http(req).success(function(res) {
                $scope.global.products_remaining--;
                
                // continue crawling
                line.content = 'Getting product information of ' + product.name + ' : DONE';
                line.done = true;
                
                if ($scope.global.products_remaining == 0) {
                    log.finish();
                    
                    // crawling cities of other countries
                    $scope.prepareProductsListing();
                    
                } else {
                    //$scope.crawlingState(stateLog);
                    $scope.getProduct(log, info, categoryIndex);
                }
            }).error(function(xhr) {
                console.log(xhr);
                log.errors.push("Error getting product information of " + product.name);
            });
            
        } else {
            console.log('State empty');
        }
    }
});