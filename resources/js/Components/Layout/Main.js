import React, { Component, Suspense } from 'react';
import { Route, Switch } from "react-router-dom";
import Loading from "../Characters";
import ErrorBoundary from "../ErrorBoundary";

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
                            <Route exact path="/" component={props => <Events {...props}/>}/>
                            <Route
                                path="/chars"
                                render={({match: {url}}) => (
                                    <>
                                        <Route exact path={url} component={props => <Characters {...props}/>}/>
                                        <Route path={url + '/create'} component={props => <CharacterForm {...props}/>}/>
                                        <Route path={url + '/:id/edit'} component={props => <CharacterForm {...props}/>}/>
                                        <Route exact path={url + '/:id/parses'} component={props => <DpsParses {...props}/>}/>
                                        <Route path={url + '/:id/parses/create'} component={props => <DpsParseForm {...props}/>}/>
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
