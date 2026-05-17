import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\OpenBidsController::index
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/open-bids',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\OpenBidsController::index
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\OpenBidsController::index
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\OpenBidsController::index
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\OpenBidsController::index
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\OpenBidsController::index
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\OpenBidsController::index
 * @see app/Http/Controllers/OpenBidsController.php:15
 * @route 'https://carrygo-optimized.test/open-bids'
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
const OpenBidsController = { index }

export default OpenBidsController