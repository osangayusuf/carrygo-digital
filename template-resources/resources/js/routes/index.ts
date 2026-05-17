import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults, validateParameters } from './../wayfinder'
/**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route 'https://carrygo-optimized.test/'
 */
export const home = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})

home.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route 'https://carrygo-optimized.test/'
 */
home.url = (options?: RouteQueryOptions) => {
    return home.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route 'https://carrygo-optimized.test/'
 */
home.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route 'https://carrygo-optimized.test/'
 */
home.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: home.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route 'https://carrygo-optimized.test/'
 */
    const homeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: home.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route 'https://carrygo-optimized.test/'
 */
        homeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: home.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route 'https://carrygo-optimized.test/'
 */
        homeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: home.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    home.form = homeForm
/**
* @see \App\Http\Controllers\OpenBidsController::openBids
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
export const openBids = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openBids.url(options),
    method: 'get',
})

openBids.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/open-bids',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\OpenBidsController::openBids
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
openBids.url = (options?: RouteQueryOptions) => {
    return openBids.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\OpenBidsController::openBids
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
openBids.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openBids.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\OpenBidsController::openBids
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
openBids.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: openBids.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\OpenBidsController::openBids
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
    const openBidsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: openBids.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\OpenBidsController::openBids
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
        openBidsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: openBids.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\OpenBidsController::openBids
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
        openBidsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: openBids.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    openBids.form = openBidsForm
/**
* @see \App\Http\Controllers\HowToPlayController::howToPlay
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
export const howToPlay = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: howToPlay.url(options),
    method: 'get',
})

howToPlay.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/how-to-play',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HowToPlayController::howToPlay
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
howToPlay.url = (options?: RouteQueryOptions) => {
    return howToPlay.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\HowToPlayController::howToPlay
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
howToPlay.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: howToPlay.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\HowToPlayController::howToPlay
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
howToPlay.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: howToPlay.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\HowToPlayController::howToPlay
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
    const howToPlayForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: howToPlay.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\HowToPlayController::howToPlay
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
        howToPlayForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: howToPlay.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\HowToPlayController::howToPlay
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
        howToPlayForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: howToPlay.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    howToPlay.form = howToPlayForm
/**
* @see \App\Http\Controllers\TermsController::terms
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
export const terms = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: terms.url(options),
    method: 'get',
})

terms.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/terms',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TermsController::terms
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
terms.url = (options?: RouteQueryOptions) => {
    return terms.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TermsController::terms
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
terms.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: terms.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TermsController::terms
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
terms.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: terms.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TermsController::terms
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
    const termsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: terms.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TermsController::terms
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
        termsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: terms.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TermsController::terms
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
        termsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: terms.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    terms.form = termsForm
/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
export const login = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(args, options),
    method: 'get',
})

login.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/login/{msisdn?}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
login.url = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { msisdn: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    msisdn: args[0],
                }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
            "msisdn",
        ])

    const parsedArgs = {
                        msisdn: args?.msisdn,
                }

    return login.definition.url
            .replace('{msisdn?}', parsedArgs.msisdn?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
login.get = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
login.head = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
    const loginForm = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: login.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
        loginForm.get = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
        loginForm.head = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    login.form = loginForm
/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::logout
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:79
 * @route 'https://carrygo-optimized.test/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::logout
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:79
 * @route 'https://carrygo-optimized.test/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::logout
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:79
 * @route 'https://carrygo-optimized.test/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::logout
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:79
 * @route 'https://carrygo-optimized.test/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::logout
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:79
 * @route 'https://carrygo-optimized.test/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
/**
* @see \App\Http\Controllers\EventBidsController::events
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
export const events = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: events.url(options),
    method: 'get',
})

events.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/events',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventBidsController::events
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
events.url = (options?: RouteQueryOptions) => {
    return events.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventBidsController::events
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
events.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: events.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\EventBidsController::events
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
events.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: events.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\EventBidsController::events
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
    const eventsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: events.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\EventBidsController::events
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
        eventsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: events.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\EventBidsController::events
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
        eventsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: events.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    events.form = eventsForm
/**
* @see \App\Http\Controllers\TrendingBidsController::trending
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
export const trending = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: trending.url(options),
    method: 'get',
})

trending.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/trending',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TrendingBidsController::trending
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
trending.url = (options?: RouteQueryOptions) => {
    return trending.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TrendingBidsController::trending
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
trending.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: trending.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TrendingBidsController::trending
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
trending.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: trending.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TrendingBidsController::trending
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
    const trendingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: trending.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TrendingBidsController::trending
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
        trendingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: trending.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TrendingBidsController::trending
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
        trendingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: trending.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    trending.form = trendingForm
/**
* @see \App\Http\Controllers\HistoryController::history
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
export const history = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HistoryController::history
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
history.url = (options?: RouteQueryOptions) => {
    return history.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\HistoryController::history
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
history.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\HistoryController::history
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
history.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\HistoryController::history
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
    const historyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: history.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\HistoryController::history
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
        historyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\HistoryController::history
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
        historyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    history.form = historyForm
/**
* @see \App\Http\Controllers\LeaderboardBidsController::leaderboard
 * @see app/Http/Controllers/LeaderboardBidsController.php:14
 * @route 'https://carrygo-optimized.test/leaderboard'
 */
export const leaderboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: leaderboard.url(options),
    method: 'get',
})

leaderboard.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/leaderboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LeaderboardBidsController::leaderboard
 * @see app/Http/Controllers/LeaderboardBidsController.php:14
 * @route 'https://carrygo-optimized.test/leaderboard'
 */
leaderboard.url = (options?: RouteQueryOptions) => {
    return leaderboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LeaderboardBidsController::leaderboard
 * @see app/Http/Controllers/LeaderboardBidsController.php:14
 * @route 'https://carrygo-optimized.test/leaderboard'
 */
leaderboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: leaderboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\LeaderboardBidsController::leaderboard
 * @see app/Http/Controllers/LeaderboardBidsController.php:14
 * @route 'https://carrygo-optimized.test/leaderboard'
 */
leaderboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: leaderboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\LeaderboardBidsController::leaderboard
 * @see app/Http/Controllers/LeaderboardBidsController.php:14
 * @route 'https://carrygo-optimized.test/leaderboard'
 */
    const leaderboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: leaderboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\LeaderboardBidsController::leaderboard
 * @see app/Http/Controllers/LeaderboardBidsController.php:14
 * @route 'https://carrygo-optimized.test/leaderboard'
 */
        leaderboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: leaderboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\LeaderboardBidsController::leaderboard
 * @see app/Http/Controllers/LeaderboardBidsController.php:14
 * @route 'https://carrygo-optimized.test/leaderboard'
 */
        leaderboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: leaderboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    leaderboard.form = leaderboardForm
/**
* @see \App\Http\Controllers\ProfileController::profile
 * @see app/Http/Controllers/ProfileController.php:11
 * @route 'https://carrygo-optimized.test/profile'
 */
export const profile = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: profile.url(options),
    method: 'get',
})

profile.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProfileController::profile
 * @see app/Http/Controllers/ProfileController.php:11
 * @route 'https://carrygo-optimized.test/profile'
 */
profile.url = (options?: RouteQueryOptions) => {
    return profile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProfileController::profile
 * @see app/Http/Controllers/ProfileController.php:11
 * @route 'https://carrygo-optimized.test/profile'
 */
profile.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: profile.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ProfileController::profile
 * @see app/Http/Controllers/ProfileController.php:11
 * @route 'https://carrygo-optimized.test/profile'
 */
profile.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: profile.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ProfileController::profile
 * @see app/Http/Controllers/ProfileController.php:11
 * @route 'https://carrygo-optimized.test/profile'
 */
    const profileForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: profile.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ProfileController::profile
 * @see app/Http/Controllers/ProfileController.php:11
 * @route 'https://carrygo-optimized.test/profile'
 */
        profileForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: profile.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ProfileController::profile
 * @see app/Http/Controllers/ProfileController.php:11
 * @route 'https://carrygo-optimized.test/profile'
 */
        profileForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: profile.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    profile.form = profileForm
/**
* @see \App\Http\Controllers\TaskCenterController::tasks
 * @see app/Http/Controllers/TaskCenterController.php:15
 * @route 'https://carrygo-optimized.test/tasks'
 */
export const tasks = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tasks.url(options),
    method: 'get',
})

tasks.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/tasks',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TaskCenterController::tasks
 * @see app/Http/Controllers/TaskCenterController.php:15
 * @route 'https://carrygo-optimized.test/tasks'
 */
tasks.url = (options?: RouteQueryOptions) => {
    return tasks.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TaskCenterController::tasks
 * @see app/Http/Controllers/TaskCenterController.php:15
 * @route 'https://carrygo-optimized.test/tasks'
 */
tasks.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tasks.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TaskCenterController::tasks
 * @see app/Http/Controllers/TaskCenterController.php:15
 * @route 'https://carrygo-optimized.test/tasks'
 */
tasks.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: tasks.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TaskCenterController::tasks
 * @see app/Http/Controllers/TaskCenterController.php:15
 * @route 'https://carrygo-optimized.test/tasks'
 */
    const tasksForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: tasks.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TaskCenterController::tasks
 * @see app/Http/Controllers/TaskCenterController.php:15
 * @route 'https://carrygo-optimized.test/tasks'
 */
        tasksForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tasks.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TaskCenterController::tasks
 * @see app/Http/Controllers/TaskCenterController.php:15
 * @route 'https://carrygo-optimized.test/tasks'
 */
        tasksForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tasks.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    tasks.form = tasksForm
/**
* @see \App\Http\Controllers\CheckinController::checkin
 * @see app/Http/Controllers/CheckinController.php:14
 * @route 'https://carrygo-optimized.test/checkin'
 */
export const checkin = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkin.url(options),
    method: 'post',
})

checkin.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/checkin',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CheckinController::checkin
 * @see app/Http/Controllers/CheckinController.php:14
 * @route 'https://carrygo-optimized.test/checkin'
 */
checkin.url = (options?: RouteQueryOptions) => {
    return checkin.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CheckinController::checkin
 * @see app/Http/Controllers/CheckinController.php:14
 * @route 'https://carrygo-optimized.test/checkin'
 */
checkin.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkin.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\CheckinController::checkin
 * @see app/Http/Controllers/CheckinController.php:14
 * @route 'https://carrygo-optimized.test/checkin'
 */
    const checkinForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: checkin.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\CheckinController::checkin
 * @see app/Http/Controllers/CheckinController.php:14
 * @route 'https://carrygo-optimized.test/checkin'
 */
        checkinForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: checkin.url(options),
            method: 'post',
        })
    
    checkin.form = checkinForm
/**
* @see \App\Http\Controllers\SpinController::spin
 * @see app/Http/Controllers/SpinController.php:14
 * @route 'https://carrygo-optimized.test/spin'
 */
export const spin = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: spin.url(options),
    method: 'post',
})

spin.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/spin',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SpinController::spin
 * @see app/Http/Controllers/SpinController.php:14
 * @route 'https://carrygo-optimized.test/spin'
 */
spin.url = (options?: RouteQueryOptions) => {
    return spin.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SpinController::spin
 * @see app/Http/Controllers/SpinController.php:14
 * @route 'https://carrygo-optimized.test/spin'
 */
spin.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: spin.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\SpinController::spin
 * @see app/Http/Controllers/SpinController.php:14
 * @route 'https://carrygo-optimized.test/spin'
 */
    const spinForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: spin.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SpinController::spin
 * @see app/Http/Controllers/SpinController.php:14
 * @route 'https://carrygo-optimized.test/spin'
 */
        spinForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: spin.url(options),
            method: 'post',
        })
    
    spin.form = spinForm