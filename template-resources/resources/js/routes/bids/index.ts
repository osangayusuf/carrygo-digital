import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\BidController::place
 * @see app/Http/Controllers/BidController.php:22
 * @route 'https://carrygo-optimized.test/bids/{bid}/place'
 */
export const place = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: place.url(args, options),
    method: 'post',
})

place.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/bids/{bid}/place',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BidController::place
 * @see app/Http/Controllers/BidController.php:22
 * @route 'https://carrygo-optimized.test/bids/{bid}/place'
 */
place.url = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return place.definition.url
            .replace('{bid}', parsedArgs.bid.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BidController::place
 * @see app/Http/Controllers/BidController.php:22
 * @route 'https://carrygo-optimized.test/bids/{bid}/place'
 */
place.post = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: place.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BidController::place
 * @see app/Http/Controllers/BidController.php:22
 * @route 'https://carrygo-optimized.test/bids/{bid}/place'
 */
    const placeForm = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: place.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BidController::place
 * @see app/Http/Controllers/BidController.php:22
 * @route 'https://carrygo-optimized.test/bids/{bid}/place'
 */
        placeForm.post = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: place.url(args, options),
            method: 'post',
        })
    
    place.form = placeForm
/**
* @see \App\Http\Controllers\HistoryController::review
 * @see app/Http/Controllers/HistoryController.php:41
 * @route 'https://carrygo-optimized.test/bids/{bid}/review'
 */
export const review = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: review.url(args, options),
    method: 'post',
})

review.definition = {
    methods: ["post"],
    url: 'https://carrygo-optimized.test/bids/{bid}/review',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\HistoryController::review
 * @see app/Http/Controllers/HistoryController.php:41
 * @route 'https://carrygo-optimized.test/bids/{bid}/review'
 */
review.url = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return review.definition.url
            .replace('{bid}', parsedArgs.bid.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\HistoryController::review
 * @see app/Http/Controllers/HistoryController.php:41
 * @route 'https://carrygo-optimized.test/bids/{bid}/review'
 */
review.post = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: review.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\HistoryController::review
 * @see app/Http/Controllers/HistoryController.php:41
 * @route 'https://carrygo-optimized.test/bids/{bid}/review'
 */
    const reviewForm = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: review.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\HistoryController::review
 * @see app/Http/Controllers/HistoryController.php:41
 * @route 'https://carrygo-optimized.test/bids/{bid}/review'
 */
        reviewForm.post = (args: { bid: number | { id: number } } | [bid: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: review.url(args, options),
            method: 'post',
        })
    
    review.form = reviewForm
const bids = {
    place: Object.assign(place, place),
review: Object.assign(review, review),
}

export default bids