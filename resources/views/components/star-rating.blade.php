<div
    x-data="{
        rating: @js($value ?? 0),
        hoverRating: null,
        setRating(value) { this.rating = value },
        setHover(value) { this.hoverRating = value },
        resetHover() { this.hoverRating = null }
    }"
    class="flex items-center space-x-1"
>
    <template x-for="star in [1,2,3,4,5]" :key="star">
        <svg
            @click="setRating(star)"
            @mouseover="setHover(star)"
            @mouseleave="resetHover()"
            :class="{
                'text-yellow-400': (hoverRating ?? rating) >= star,
                'text-gray-300': (hoverRating ?? rating) < star
            }"
            class="w-8 h-8 cursor-pointer transition-colors"
            fill="currentColor"
            viewBox="0 0 20 20"
        >
            <polygon points="9.9,1.1 12.3,6.6 18.2,7.3 13.7,11.2 15,17 9.9,14.1 4.8,17 6.1,11.2 1.6,7.3 7.5,6.6"/>
        </svg>
    </template>
    <input type="hidden" x-model="rating" name="{{ $name ?? 'star_rating' }}" />
</div>
