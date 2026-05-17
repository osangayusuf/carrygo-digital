import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EventBidsController::index
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/events',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventBidsController::index
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventBidsController::index
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\EventBidsController::index
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\EventBidsController::index
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\EventBidsController::index
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\EventBidsController::index
 * @see app/Http/Controllers/EventBidsController.php:15
 * @route 'https://carrygo-optimized.test/events'
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
const EventBidsController = { index }

export default EventBidsController