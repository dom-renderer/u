@extends('layouts.app-master')

@push('css')
    <style>
        :root {
            --c-body-font-clr: #000;
            --c-primary-focus-color: #03a9f5;
            --c-primary-anchor-clr: #03a9f5;
            --c-primary-btn-bg: #f0483e;
            --c-white: #fff;
            --c-zsh-place-fontcolor: #878787;
            --c-zf-hover: #f6f8fb;
            --c-highlight: #448FF7;
            --c-primary-color: #272F43;
            --c-top-bar-color: #262F45;
            --c-cta: #1AB394;
            --c-cta-padding: 12px 20px;
            --c-dark-text: #333333;
            --c-text-color: #272F43;
            --c-b-radius: 3px;
            --c-fontsize14: 14px;
            --c-fontsize16: 16px;
            --c-fontsize12: 12px;
            --c-gray: #555555;
            --c-body-bg: #F5F6F8;
            --c-discard: #A8A8A8;
            --c-gray-100: #ECECEC;
            --c-gray-60: #9d9d9d;
            --c-gray-50: #f5f6f8;
            --c-gray-10: #bbbbbb;
            --c-gray-200: #d6d4d4;
            --c-light-gray: #F4F4F4;
            --c-dark-blue-gray: #464D5D;
            --c-charcoal-blue: #464D5D;
            --c-sonic-silver: #7C7C7C;
            --c-light-black: rgba(0, 0, 0, 0.7);
            --c-silver-gray: #D3D3D3;
            --c-corl: #ff6347;
            --c-off-gray: #FAFAFA;
            --c-mist-slate: #DDDDDD80;
            --c-dark-charcoal: #333;
            --c-soft-silver: #E3E3E3;
            --c-granite-gray: #848484;
            --c-red: red;
            --c-teal: #20A58A;
            --c-celadon-green: #52AD93;
            --c-dodger-blue: #2680EB;
            --c-alice-blue: #edf4fd;
            --c-border-gray: #ddd;
            --c-pale-gray: #eee;
            --c-vibrant-red: #EF4C4C;
            --c-aliceBlue: #ECF4FE;
            --c-mintcream: #EEF6F5;
            --c-drak-sea-green: #88bcaa;
            --c-keppel: #36bc9e;
        }

        ul li {
            list-style: none
        }


        .tree {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-left: 46px;
            position: relative;
        }

        .tree .parent {
            position: relative;
            padding-block: 4px;
        }

        .tree .parent::before,
        .tree .parent::after {
            content: '';
            position: absolute;

        }


        .tree .parent::before {
            width: 18px;
            top: 13px;
            left: -18px;
            border-bottom: 1px dashed var(--c-gray-200);
        }

        .tree .parent::after {
            display: block;
            width: 0;
            top: 0;
            left: -27.5px;
            height: 39px;
            border-left: 1px solid var(--c-gray-200);
            bottom: 0;
        }


        .tree .parent:last-child::after {
            height: 5px;
            bottom: auto;
        }

        .tree .parent::after {
            height: 100%;
            top: 23px;
        }

        .tree details[open]>.parent::before,
        .tree details[open]>.nested-item::after {
            border-left-color: var(--c-drak-sea-green);
        }

        .tree .parent.parent:last-child::after {
            content: none;
            bottom: auto;
        }

        .tree details {
            margin: 0 auto;
        }

        .details>.nested-list {
            padding: 15px;
        }

        .tree summary {
            display: block;
            position: relative;
            cursor: pointer;
        }

        .tree summary::before {
            content: '+';
            position: absolute;
            top: 0;
            left: -37px;
            width: 18px;
            height: 18px;
            text-align: center;
            color: var(--c-gray-200);
            border: 1px solid var(--c-gray-200);
            border-radius: 50%;
            background-color: var(--c-white);
            z-index: 10;
        }


        .tree::after {
            content: '';
            position: absolute;
            top: -18px;
            left: -27px;
            width: 1px;
            height: 22px;
            background-color: var(--c-gray-200);

        }


        .tree details[open]>summary::before {
            content: '-';
            border: 1px solid var(--c-drak-sea-green);
            color: var(--c-drak-sea-green);
        }


        .tree details[open]>.tree.parent::after {
            display: block;
            width: 0;
            top: 0;
            left: -27.5px;
            height: 39px;
            border-left: 1px solid var(--c-drak-sea-green);
            /* Change border color here */
            bottom: 0;
        }


        .tree .nested-list {
            padding: 0 0 0 45px;
        }

        .tree .nested-item {
            position: relative;
            padding: 10px 0 0 0;
            margin-top: 0;
        }

        .tree .nested-item::before,
        .tree .nested-item::after {
            content: '';
            position: absolute;
            left: -17px;
        }


        .tree .nested-item::before {
            width: 28px;
            top: 18px;
            left: -27px;
            border-bottom: 1px dashed var(--c-gray-200);
        }

        .tree .nested-item::after {
            display: block;
            width: 0;
            top: 0;
            left: -27px;
            height: 45px;
            height: 100%;
            border-left: 1px solid var(--c-gray-200);
            bottom: 0;
        }

        .tree .nested-item:last-child::after {
            top: 0px;
            height: 19px;
            bottom: auto;
        }

        .tree .label {
            display: inline-block;
            font-size: 10px;
            padding-inline: 3px;
            color: #929292;
            margin-right: 10px;
            border: 1px solid var(--c-gray-100);
        }

        .tree>.parent:last-child {
            padding-bottom: 0;
        }

        .tree-title {
            font-size: 12px;
        }

        .tree-title:hover {
            color: var(--c-keppel);
            cursor: pointer;

        }
    </style>
@endpush

@section('content')
    <div class="bg-light p-4 rounded">
       
        <div class="mx-w-700 mx-auto mt-4">
            <div class="card mb-4">
                <div class="card-body">



                    <ul class="tree">
                        <li class="parent">
                            <details open class="details">
                                <summary> {{ $category->name }} </summary>
                                @if(!$category->children->isEmpty())
                                <ul class="nested-list">
                                    @forelse ($category->children as $cat)
                                        @if(!$cat->children->isEmpty())
                                            @include('topics.sub-category', ['c' => $cat, 'name' => $cat->name])
                                        @else
                                            <li class="nested-item"> {{ $cat->name }} </li>
                                        @endif
                                    @empty                                        
                                    @endforelse
                                </ul>
                                @endif
                            </details>
                        </li>
                    </ul>


                </div>
            </div>
        </div>

        <a href="{{ route('topics.index') }}" class="btn btn-primary"> Back </a>
    </div>
@endsection
