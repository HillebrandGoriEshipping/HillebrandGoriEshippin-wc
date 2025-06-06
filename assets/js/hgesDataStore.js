import { register, createReduxStore } from '@wordpress/data';

const DEFAULT_STATE = {
    selectedRate: null,
    selectedPickupPoint: null,
};

const actions = {
    setSelectedRate(rate) {
        return {
            type: 'SET_SELECTED_RATE',
            rate,
        };
    },

    setSelectedPickupPoint(pickupPoint) {
        return {
            type: 'SET_SELECTED_PICKUP_POINT',
            pickupPoint,
        };
    },
};

const reducer = (state = DEFAULT_STATE, action) => {
    switch (action.type) {
        case 'SET_SELECTED_RATE':
            return {
                ...state,
                selectedRate: action.rate,
            };
        case 'SET_SELECTED_PICKUP_POINT':
            return {
                ...state,
                selectedPickupPoint: action.pickupPoint,
            };
        default:
            return state;
    }
}

const selectors = {
    getSelectedRate(state) {
        return state.selectedRate;
    },

    getSelectedPickupPoint(state) {
        return state.selectedPickupPoint;
    },
};

const store = createReduxStore('hges/main', {
    reducer,
    actions,
    selectors,
    resolvers: {},
});

register(store);