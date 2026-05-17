import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults, validateParameters } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::show
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
export const show = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/login/{msisdn?}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::show
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
show.url = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{msisdn?}', parsedArgs.msisdn?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::show
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
show.get = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::show
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
show.head = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::show
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
    const showForm = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::show
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
        showForm.get = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::show
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:17
 * @route 'https://carrygo-optimized.test/login/{msisdn?}'
 */
        showForm.head = (args?: { msisdn?: string | number } | [msisdn: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:28
 * @route 'https://carrygo-optimized.test/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:28
 * @route 'https://carrygo-optimized.test/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:28
 * @route 'https://carrygo-optimized.test/login'
 */
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:28
 * @route 'https://carrygo-optimized.test/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: login.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\MsisdnLoginController::login
 * @see app/Http/Controllers/Auth/MsisdnLoginController.php:28
 * @route 'https://carrygo-optimized.test/login'
 */
        loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: login.url(options),
            method: 'post',
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
const MsisdnLoginController = { show, login, logout }

export default MsisdnLoginController