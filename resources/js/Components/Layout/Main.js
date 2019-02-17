import React, { Component, Suspense } from 'react';
import { Route, Switch } from "react-router-dom";
import Loading from "../Characters";
import ErrorBoundary from "../ErrorBoundary";

// const Events = Loadable({
//     loader: () => import(
//         /* webpackPrefetch: true */
//         /* webpackChunkName: "components-events" */
//         '../Events'),
//     loading: () => <Loading/>
// });
// const Characters = Loadable({
//     loader: () => import(
//         /* webpackPrefetch: true */
//         /* webpackChunkName: "components-characters" */
//         '../Characters'),
//     loading: () => <Loading/>
// });
// const CharacterForm = Loadable({
//     loader: () => import(
//         /* webpackPrefetch: true */
//         /* webpackChunkName: "components-character_form" */
//         '../Forms/CharacterForm'),
//     loading: () => <Loading/>
// });
// const DpsParseForm = Loadable({
//     loader: () => import(
//         /* webpackPrefetch: true */
//         /* webpackChunkName: "components-dps_parse_form" */
//         '../Forms/DpsParseForm'),
//     loading: () => <Loading/>
// });

const Events = React.lazy(() => import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "components-events" */
    '../Events')
);
const Characters = React.lazy(() => import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "components-characters" */
    '../Characters'));
const CharacterForm = React.lazy(() => import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "components-character_form" */
    '../Forms/CharacterForm')
);
const DpsParseForm = React.lazy(() => import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "components-dps_parse_form" */
    '../Forms/DpsParseForm')
);

class Main extends Component {
    render = () => {
        return [
            <main key="main" className="container">
                <Suspense fallback={<Loading/>}>
                    <ErrorBoundary>
                        <Switch>
                            <Route exact path="/" component={() => <Characters/>}/>
                            <Route path="/events" component={() => <Events/>}/>
                            <Route
                                path="/chars"
                                render={({match: {url}}) => (
                                    <>
                                        <Route exact path={url} component={() => <Characters/>}/>
                                        <Route path={url + '/create'} component={() => <CharacterForm/>}/>
                                        <Route path={url + '/:id/edit'} component={() => <CharacterForm/>}/>
                                        <Route path={url + '/:id/parses/create'} component={() => <DpsParseForm/>}/>
                                    </>
                                )}
                            />
                            <Route component={ErrorBoundary}/>
                        </Switch>
                    </ErrorBoundary>
                </Suspense>
            </main>,
        ];
    };
}

export default Main;
