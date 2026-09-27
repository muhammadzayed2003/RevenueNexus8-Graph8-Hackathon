@props([
    'name' => 'Revenue Nexus',
    'role' => 'AI simulation agent',
    'accent' => '#eee9df',
    'size' => 'medium',
    'state' => 'idle',
    'speakerId' => null,
    'showIdentity' => true,
    'entrance' => false,
])

@php
    $settings = [
        'name' => $name,
        'accent' => '#eee9df',
        'state' => $state,
        'speakerId' => $speakerId,
        'entrance' => (bool) $entrance,
    ];

    $safeSize = in_array(
        $size,
        ['small', 'medium', 'large', 'hero'],
        true
    ) ? $size : 'medium';
@endphp

<div
    {{ $attributes->class([
        'rt3d-nexus',
        'rt3d-nexus--'.$safeSize,
    ]) }}
    x-data="revenueRobot({{
        Illuminate\Support\Js::from($settings)
    }})"
    x-on:rt-speaker.window="changeSpeaker($event.detail)"
    x-bind:class="{
        'rt3d-nexus--speaking': active
    }"
>
    <div
        x-ref="viewport"
        class="rt3d-nexus__viewport"
    ></div>

    <div
        x-cloak
        x-show="failed"
        class="rt3d-nexus__error"
        role="status"
    >
        3D preview unavailable on this device.
        Simulation results remain accessible.
    </div>

    @if($showIdentity)
        <div class="rt3d-nexus__identity">
            <i></i>

            <div>
                <strong>
                    {{ $name }}
                </strong>

                <small>
                    {{ $role }}
                </small>
            </div>

            <span
                x-cloak
                x-show="active"
            >
                Speaking
            </span>
        </div>
    @endif
</div>

