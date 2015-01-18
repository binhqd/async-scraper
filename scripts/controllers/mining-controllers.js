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
        countries : [],
        cachedCountries : [],
        country : {}
    };
    
    var req = {
        method : 'GET',
        url : './api.php?round=round1'
    }

    $scope.state = {
    // gettingChapter : false
    };

    $scope.done = false;

    $scope.global.logs.push(new MiningLog({
        "title" : "Getting list of countries"
    }));
    $http(req).success(function(res) {
        var log = $scope.global.logs[0];
        log.title = 'List of countries';
        log.data = res.items;
        log.lines.push({content: res.total + " countries found"});
        log.finish();

        // Processing round 2
        var stateLog = new MiningLog({
            "title" : "Crawling states"
        });
        
        // save cache for later use
        $scope.global.cachedCountries = angular.copy(res.items);
        
        $scope.global.countries = res.items;

        $scope.global.logs.push(stateLog);
        $scope.global.round2_remaining = $scope.global.countries.length;
        
        // Open 5 threads
        $scope.crawlingState(stateLog);
        $scope.crawlingState(stateLog);
        $scope.crawlingState(stateLog);
        $scope.crawlingState(stateLog);
        $scope.crawlingState(stateLog);

    }).error(function(xhr) {
        console.log(xhr);
    });

    $scope.crawlingState = function(stateLog, callback) {
        if ($scope.global.countries.length > 0) {
            
            var country = $scope.global.countries[0];
            $scope.global.countries.shift();
            
            var req = {
                method : 'GET',
                url : './api.php?round=round2&countryID=' + country.value + '&countryName='+encodeURIComponent(country.text)
            }
            
            var line = {content: country.text + ': crawling ...', done : false};
            stateLog.lines.push(line);
            $http(req).success(function(res) {
                $scope.global.round2_remaining--;
                
                // continue crawling
                line.content = country.text + ": crawled";
                line.done = true;
                
                if ($scope.global.round2_remaining == 0) {
                    stateLog.finish();
                    
                    // processing parse states
                    $scope.global.countries = angular.copy($scope.global.cachedCountries);
                    $scope.prepareStateData();
                    
                } else {
                    $scope.crawlingState(stateLog);
                }
            }).error(function(xhr) {
                console.log(xhr);
                stateLog.errors.push("Error crawling " + country.text);
            });
        } else {
            //stateLog.finish();
            
            // crawling cities of states
        }
    }

    $scope.prepareStateData = function() {
        if ($scope.global.countries.length > 0) {
            
            var country = $scope.global.countries[0];
            $scope.global.countries.shift();
            
            var log = new MiningLog({
                "title" : "Crawling cities of " + country.text
            });
            $scope.global.logs.push(log);
            
            var line = {content: 'Getting states of ' + country.text, done : false};
            log.lines.push(line);
            
            var req = {
                method : 'GET',
                url : './api.php?round=getStates&country=' + encodeURIComponent(country.text)
            }
            $http(req).success(function(res) {
                
                // continue crawling
                line.content = 'Getting state of ' + country.text + ' : DONE';
                line.done = true;
                
                // assign to global variable
                
                $scope.global.country[country.value] = {
                    states : res.states,
                    country : country.text
                };
                
                $scope.global.states_remaining = res.states.length;
                
                // 5x running
                $scope.getCities(log, country.value);
                $scope.getCities(log, country.value);
                $scope.getCities(log, country.value);
                $scope.getCities(log, country.value);
                $scope.getCities(log, country.value);
                
            }).error(function(xhr) {
                console.log(xhr);
                log.errors.push("Error preparing info for " + country.text);
            });
        } else {
            //stateLog.finish();
            
            // crawling cities of states
        }
    }
    
    $scope.getCities = function(log, countryID) {
        if ($scope.global.country[countryID].states.length > 0) {
            var country = $scope.global.country[countryID];
            
            var state = country.states[0];
            
            country.states.shift();
            
            var line = {content: 'Getting cities of ' + state.text, done : false};
            log.lines.push(line);
            
            var req = {
                method : 'GET',
                url : './api.php?round=getCities&country=' + encodeURIComponent(country.country)+'&state=' + encodeURIComponent(state.text)
                + '&stateID=' + state.value
            }
            
            $http(req).success(function(res) {
                $scope.global.states_remaining--;
                
                // continue crawling
                line.content = 'Getting cities of ' + state.text + ' : DONE';
                line.done = true;
                
                if ($scope.global.states_remaining == 0) {
                    log.finish();
                    
                    // crawling cities of other countries
                    $scope.prepareStateData();
                    
                } else {
                    //$scope.crawlingState(stateLog);
                    $scope.getCities(log, countryID);
                }
            }).error(function(xhr) {
                console.log(xhr);
                log.errors.push("Error preparing info for " + country.country);
            });
            
        } else {
            console.log('State empty');
        }
    }
    
    $scope.getBook = function(callback) {
        var book = {};
        if ($scope.global.books.length > 0) {
            book = $scope.global.books[0];
            $scope.global.currentBook = book;

            $scope.getChapters(book, function() {
                // get another books

                if (!$scope.state.gettingChapter)
                    $scope.getBook(callback);
            });
            $scope.global.books.shift();
        } else {
            callback();
        }
    }

    $scope.getVerses = function(callback) {
        // TODO: Check if chapters is empty
        if ($scope.global.chapters.length == 0) {
            callback();
        } else {
            var chapter = $scope.global.chapters[0];;
            $scope.global.currentChapter = chapter;
            $scope.global.chapters.shift();

            var url = './book-content.php?url=' + bookUri
                + encodeURIComponent(chapter.href);
            var req = {
                method : 'GET',
                url : url
            }

            $http(req).success(function(res) {
                $scope.getVerses(callback);
            }).error(function(xhr) {
                $scope.global.errors.push({
                    book : $scope.global.currentBook,
                    chapter : chapter,
                    scenario : 'getVerses',
                    url : url,
                    originalUrl : bookUri + chapter.href
                });

                console.log("Can't get chapters of '" + chapter.text + "'");
                console.log(xhr);

                $scope.getVerses(callback);
            });
        }
    }

    $scope.getChapters = function(book, callback) {
        var url = './chapters.php?url=' + bookUri
            + encodeURIComponent(book.href);
        var req = {
            method : 'GET',
            url : url
        }
        var chapter;

        $scope.state.gettingChapter = true;
        $http(req).success(function(res) {
            $scope.global.chapters = res.chapters;

            $scope.state.gettingChapter = false;
            $scope.global.totalChapter = $scope.global.chapters.length;

            $scope.getVerses(callback);

            $scope.getVerses(callback);

            $scope.getVerses(callback);

            $scope.getVerses(callback);

            $scope.getVerses(callback);

        }).error(function() {
            $scope.global.errors.push({
                book : book,
                // chapter => chapter,
                scenario : 'getChapters',
                url : url,
                originalUrl : bookUri + book.href
            });
            console.log("Can't get chapters of '" + chapter.text + "'");
            console.log(xhr);

            // get another books
            $scope.getBook();
        });
    }
});