import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\TermsController::index
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://carrygo-optimized.test/terms',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TermsController::index
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TermsController::index
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TermsController::index
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TermsController::index
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TermsController::index
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TermsController::index
 * @see app/Http/Controllers/TermsController.php:13
 * @route 'https://carrygo-optimized.test/terms'
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
const TermsController = { index }

export default TermsController