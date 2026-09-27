@props(['deals'])

<div class="space-y-5">
    <div>
        <label
            for="deal_record_id"
            class="mb-2 block text-sm font-medium"
        >
            graph8 deal — optional
        </label>

        <select
            id="deal_record_id"
            name="deal_record_id"
            class="w-full"
        >
            <option value="">
                Standalone simulation — no linked deal
            </option>

            @foreach($deals as $deal)
                <option
                    value="{{ $deal->id }}"
                    @selected(
                        (string) old('deal_record_id') ===
                        (string) $deal->id
                    )
                >
                    {{ $deal->name ?: $deal->external_id }}
                </option>
            @endforeach
        </select>

        <p class="mt-2 text-xs text-slate-400">
            The selected deal is fetched from graph8 when you run.
            The offer details below define the proposal being tested.
        </p>

        @error('deal_record_id')
            <p class="mt-2 text-xs text-rose-400">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="product_name"
            class="mb-2 block text-sm font-medium"
        >
            Product or service
        </label>

        <input
            id="product_name"
            name="product_name"
            type="text"
            required
            maxlength="255"
            value="{{ old('product_name') }}"
            placeholder="Name of the offer being negotiated"
            class="w-full"
        >

        @error('product_name')
            <p class="mt-2 text-xs text-rose-400">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="product_details"
            class="mb-2 block text-sm font-medium"
        >
            Verified product details and scope
        </label>

        <textarea
            id="product_details"
            name="product_details"
            required
            rows="4"
            maxlength="10000"
            placeholder="What is included? Which features and benefits can you actually support? State exclusions too."
            class="w-full"
        >{{ old('product_details') }}</textarea>

        @error('product_details')
            <p class="mt-2 text-xs text-rose-400">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="commercial_terms"
            class="mb-2 block text-sm font-medium"
        >
            Commercial terms
        </label>

        <textarea
            id="commercial_terms"
            name="commercial_terms"
            required
            rows="3"
            maxlength="5000"
            placeholder="USD annual or one-time price, payment schedule, implementation terms, support and exclusions. Mark unknown details as unknown."
            class="w-full"
        >{{ old('commercial_terms') }}</textarea>

        @error('commercial_terms')
            <p class="mt-2 text-xs text-rose-400">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="allowed_concessions"
            class="mb-2 block text-sm font-medium"
        >
            Authorised concessions
        </label>

        <textarea
            id="allowed_concessions"
            name="allowed_concessions"
            required
            rows="3"
            maxlength="5000"
            placeholder="One approved concession per line. Write None if nothing is authorised."
            class="w-full"
        >{{ old('allowed_concessions') }}</textarea>

        <p class="mt-2 text-xs text-slate-400">
            Include price-discount permission explicitly if allowed.
            A minimum price alone does not authorise a discount.
        </p>

        @error('allowed_concessions')
            <p class="mt-2 text-xs text-rose-400">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="non_negotiables"
            class="mb-2 block text-sm font-medium"
        >
            Non-negotiable conditions — optional
        </label>

        <textarea
            id="non_negotiables"
            name="non_negotiables"
            rows="3"
            maxlength="5000"
            placeholder="Promises the seller must not make, excluded features, payment restrictions, or other firm boundaries."
            class="w-full"
        >{{ old('non_negotiables') }}</textarea>

        @error('non_negotiables')
            <p class="mt-2 text-xs text-rose-400">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>