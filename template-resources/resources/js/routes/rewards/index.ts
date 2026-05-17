import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\RewardClaimController::claim
 * @see app/Http/Controllers/RewardClaimController.php:14
 * @route 'https://carrygo-optimized.test/rewards/claim'
 */
export const claim = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: claim.url(options),
    method: 'post',
})

claim.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/rewards/claim',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\RewardClaimController::claim
 * @see app/Http/Controllers/RewardClaimController.php:14
 * @route 'https://carrygo-optimized.test/rewards/claim'
 */
claim.url = (options?: RouteQueryOptions) => {
    return claim.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RewardClaimController::claim
 * @see app/Http/Controllers/RewardClaimController.php:14
 * @route 'https://carrygo-optimized.test/rewards/claim'
 */
claim.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: claim.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\RewardClaimController::claim
 * @see app/Http/Controllers/RewardClaimController.php:14
 * @route 'https://carrygo-optimized.test/rewards/claim'
 */
    const claimForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: claim.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\RewardClaimController::claim
 * @see app/Http/Controllers/RewardClaimController.php:14
 * @route 'https://carrygo-optimized.test/rewards/claim'
 */
        claimForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: claim.url(options),
            method: 'post',
        })
    
    claim.form = claimForm
const rewards = {
    claim: Object.assign(claim, claim),
}

export default rewards