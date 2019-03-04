import React, { Component, Fragment, Suspense } from 'react';
import { Route, Switch } from "react-router-dom";
import Loading from "../Characters";
import ErrorBoundary from "../ErrorBoundary";

const Home = React.lazy(() => import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "components-home" */
    '../Home')
);

// const Events = React.lazy(() => import(
//     /* webpackPrefetch: true */
//     /* webpackChunkName: "components-events" */
//     '../Events')
// );

const Characters = React.lazy(() => import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "components-characters" */
    '../Characters'));
const CharacterForm = React.lazy(() => import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "components-character_form" */
    '../Forms/CharacterForm')
);
const DpsParses = React.lazy(() => import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "components-dps-parses" */
    '../DpsParses'));
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
                            <Route exact path="/" component={props => <Home {...props}/>}/>
                            {/*<Route exact path="/calendar" component={props => <Events {...props}/>}/>*/}
                            <Route
                                path="/chars"
                                render={({match: {url}}) => (
                                    <Fragment>
                                        <Route exact path={url} component={props => <Characters {...props}/>}/>
                                        <Route path={url + '/create'} component={props => <CharacterForm {...props}/>}/>
                                        <Route path={url + '/:id/edit'} component={props => <CharacterForm {...props}/>}/>
                                        <Route exact path={url + '/:id/parses'} component={props => <DpsParses {...props}/>}/>
                                        <Route path={url + '/:id/parses/create'} component={props => <DpsParseForm {...props}/>}/>
                                    </Fragment>
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
