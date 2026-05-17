import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\HowToPlayController::index
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/how-to-play',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HowToPlayController::index
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\HowToPlayController::index
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\HowToPlayController::index
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\HowToPlayController::index
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\HowToPlayController::index
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\HowToPlayController::index
 * @see app/Http/Controllers/HowToPlayController.php:10
 * @route 'https://carrygo-optimized.test/how-to-play'
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
const HowToPlayController = { index }

export default HowToPlayController