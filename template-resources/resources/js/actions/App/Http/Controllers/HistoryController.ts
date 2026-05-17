import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\HistoryController::index
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HistoryController::index
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\HistoryController::index
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\HistoryController::index
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\HistoryController::index
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\HistoryController::index
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\HistoryController::index
 * @see app/Http/Controllers/HistoryController.php:16
 * @route 'https://carrygo-optimized.test/history'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\HistoryController::storeReview
 * @see app/Http/Controllers/HistoryController.php:41
 * @route 'https://carrygo-optimized.test/bids/{bid}/review'
 */
export const storeReview = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeReview.url(args, options),
    method: 'post',
})

storeReview.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/bids/{bid}/review',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\HistoryController::storeReview
 * @see app/Http/Controllers/HistoryController.php:41
 * @route 'https://carrygo-optimized.test/bids/{bid}/review'
 */
storeReview.url = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bid: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { bid: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    bid: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        bid: typeof args.bid === 'object'
                ? args.bid.id
                : args.bid,
                }

    return storeReview.definition.url
            .replace('{bid}', parsedArgs.bid.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\HistoryController::storeReview
 * @see app/Http/Controllers/HistoryController.php:41
 * @route 'https://carrygo-optimized.test/bids/{bid}/review'
 */
storeReview.post = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeReview.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\HistoryController::storeReview
 * @see app/Http/Controllers/HistoryController.php:41
 * @route 'https://carrygo-optimized.test/bids/{bid}/review'
 */
    const storeReviewForm = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeReview.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\HistoryController::storeReview
 * @see app/Http/Controllers/HistoryController.php:41
 * @route 'https://carrygo-optimized.test/bids/{bid}/review'
 */
        storeReviewForm.post = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeReview.url(args, options),
            method: 'post',
        })
    
    storeReview.form = storeReviewForm
const HistoryController = { index, storeReview }

export default HistoryController