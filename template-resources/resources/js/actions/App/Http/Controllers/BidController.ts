import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BidController::store
 * @see app/Http/Controllers/BidController.php:22
 * @route 'https://carrygo-optimized.test/bids/{bid}/place'
 */
export const store = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/bids/{bid}/place',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BidController::store
 * @see app/Http/Controllers/BidController.php:22
 * @route 'https://carrygo-optimized.test/bids/{bid}/place'
 */
store.url = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return store.definition.url
            .replace('{bid}', parsedArgs.bid.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BidController::store
 * @see app/Http/Controllers/BidController.php:22
 * @route 'https://carrygo-optimized.test/bids/{bid}/place'
 */
store.post = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BidController::store
 * @see app/Http/Controllers/BidController.php:22
 * @route 'https://carrygo-optimized.test/bids/{bid}/place'
 */
    const storeForm = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BidController::store
 * @see app/Http/Controllers/BidController.php:22
 * @route 'https://carrygo-optimized.test/bids/{bid}/place'
 */
        storeForm.post = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(args, options),
            method: 'post',
        })
    
    store.form = storeForm
const BidController = { store }

export default BidController