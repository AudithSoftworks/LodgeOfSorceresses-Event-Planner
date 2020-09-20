import { routerMiddleware } from 'connected-react-router';
import { createBrowserHistory } from 'history';
import { applyMiddleware, compose, createStore } from 'redux';
import { createLogger } from 'redux-logger';
import thunk from 'redux-thunk';
import createRootReducer from './reducers';

export const history = createBrowserHistory();

const configureStore = preloadedState => {
    const middlewares = [thunk];
    middlewares.push(routerMiddleware(history));
    if (process.env.NODE_ENV !== 'production') {
        middlewares.push(createLogger());
    }

    // noinspection JSUnresolvedVariable
    const composeEnhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
    return createStore(
        createRootReducer(history),
        preloadedState,
        composeEnhancer(applyMiddleware(...middlewares)),
    );
};

export default configureStore;
