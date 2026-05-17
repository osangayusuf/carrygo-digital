import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\TrendingBidsController::index
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/trending',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TrendingBidsController::index
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TrendingBidsController::index
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TrendingBidsController::index
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TrendingBidsController::index
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TrendingBidsController::index
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TrendingBidsController::index
 * @see app/Http/Controllers/TrendingBidsController.php:16
 * @route 'https://carrygo-optimized.test/trending'
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
const TrendingBidsController = { index }

export default TrendingBidsController