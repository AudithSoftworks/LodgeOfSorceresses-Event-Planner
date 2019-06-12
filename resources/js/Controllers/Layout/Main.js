import React, { Component, Fragment, Suspense } from 'react';
import { Route, Switch } from 'react-router-dom';
import Loading from '../Loading';
import NoMatch from '../NoMatch';

const Init = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-init" */
        '../Init'
    )
);
const Home = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-home" */
        '../Home'
    )
);
const Character = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-character-sheet" */
        '../Character'
    )
);
const Characters = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-my-characters" */
        '../Characters'
    )
);
const CharacterForm = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-character_form" */
        '../Forms/CharacterForm'
    )
);
const DpsParses = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-dps-parses" */
        '../DpsParses'
    )
);
const DpsParseForm = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-dps_parse_form" */
        '../Forms/DpsParseForm'
    )
);

const AdminHome = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-admin-home" */
        '../Admin/Home'
    )
);
const AdminCharacters = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-admin-characters" */
        '../Admin/Characters'
    )
);
const AdminDpsParses = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-admin-dps-parses" */
        '../Admin/DpsParses'
    )
);

class Main extends Component {
    render = () => {
        return [
            <main key="main" className="container">
                <Suspense fallback={<Loading />}>
                    <Switch>
                        <Route exact path="/" component={props => <Init {...props} />} />
                        <Route exact path="/dashboard" component={props => <Home {...props} />} />
                        <Route exact path="/characters/:id" component={props => <Character {...props} />} />
                        <Route
                            path="/@me/characters"
                            render={({ match: { url } }) => (
                                <Fragment>
                                    <Route exact path={url} component={props => <Characters {...props} />} />
                                    <Route path={url + '/create'} component={props => <CharacterForm {...props} />} />
                                    <Route path={url + '/:id/edit'} component={props => <CharacterForm {...props} />} />
                                    <Route exact path={url + '/:id/parses'} component={props => <DpsParses {...props} />} />
                                    <Route path={url + '/:id/parses/create'} component={props => <DpsParseForm {...props} />} />
                                </Fragment>
                            )}
                        />
                        <Route
                            path="/admin"
                            render={({ match: { url } }) => (
                                <Fragment>
                                    <Route exact path={url} component={props => <AdminHome {...props} />} />
                                    <Route exact path={url + '/characters'} component={props => <AdminCharacters {...props} />} />
                                    <Route path={url + '/parses'} component={props => <AdminDpsParses {...props} />} />
                                </Fragment>
                            )}
                        />
                        <Route component={NoMatch} />
                    </Switch>
                </Suspense>
            </main>,
        ];
    };
}

export default Main;
