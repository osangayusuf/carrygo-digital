import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\RewardClaimController::store
 * @see app/Http/Controllers/RewardClaimController.php:14
 * @route 'https://carrygo-optimized.test/rewards/claim'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/rewards/claim',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\RewardClaimController::store
 * @see app/Http/Controllers/RewardClaimController.php:14
 * @route 'https://carrygo-optimized.test/rewards/claim'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RewardClaimController::store
 * @see app/Http/Controllers/RewardClaimController.php:14
 * @route 'https://carrygo-optimized.test/rewards/claim'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\RewardClaimController::store
 * @see app/Http/Controllers/RewardClaimController.php:14
 * @route 'https://carrygo-optimized.test/rewards/claim'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\RewardClaimController::store
 * @see app/Http/Controllers/RewardClaimController.php:14
 * @route 'https://carrygo-optimized.test/rewards/claim'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const RewardClaimController = { store }

export default RewardClaimController