import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SpinController::store
 * @see app/Http/Controllers/SpinController.php:14
 * @route 'https://carrygo-optimized.test/spin'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/spin',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SpinController::store
 * @see app/Http/Controllers/SpinController.php:14
 * @route 'https://carrygo-optimized.test/spin'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SpinController::store
 * @see app/Http/Controllers/SpinController.php:14
 * @route 'https://carrygo-optimized.test/spin'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\SpinController::store
 * @see app/Http/Controllers/SpinController.php:14
 * @route 'https://carrygo-optimized.test/spin'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SpinController::store
 * @see app/Http/Controllers/SpinController.php:14
 * @route 'https://carrygo-optimized.test/spin'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const SpinController = { store }

export default SpinController