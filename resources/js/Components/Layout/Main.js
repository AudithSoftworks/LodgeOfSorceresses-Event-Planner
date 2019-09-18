import React, { Component, Fragment, Suspense } from 'react';
import { Route, Switch } from 'react-router-dom';
import Loading from '../Loading';
import NoMatch from '../../Controllers/NoMatch';

const Init = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-init" */
        '../../Controllers/Init'
    )
);
const Home = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-home" */
        '../../Controllers/Home'
    )
);
const Users = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-users" */
        '../../Controllers/Users'
        )
);
const Character = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-character-sheet" */
        '../../Controllers/Character'
    )
);
const Characters = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-my-characters" */
        '../../Controllers/Characters'
    )
);
const CharacterForm = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-character_form" */
        '../../Controllers/Forms/CharacterForm'
    )
);
const DpsParses = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-dps-parses" */
        '../../Controllers/DpsParses'
    )
);
const DpsParseForm = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-dps_parse_form" */
        '../../Controllers/Forms/DpsParseForm'
    )
);
const Events = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-events" */
        '../../Controllers/Events'
    )
);
const EventForm = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-event_form" */
        '../../Controllers/Forms/EventForm'
    )
);

const AdminHome = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-admin-home" */
        '../../Controllers/Admin/Home'
    )
);
const AdminCharacters = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-admin-characters" */
        '../../Controllers/Admin/Characters'
    )
);
const AdminDpsParses = React.lazy(() =>
    import(
        /* webpackPrefetch: true */
        /* webpackChunkName: "controllers-admin-dps-parses" */
        '../../Controllers/Admin/DpsParses'
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
                        <Route exact path="/users" component={props => <Users {...props} />} />
                        <Route exact path="/characters/:id" component={props => <Character {...props} />} />
                        <Route exact path="/events" component={props => <Events {...props} />} />
                        <Route exact path="/events/create" component={props => <EventForm {...props} />} />
                        <Route
                            path="/@me/characters"
                            render={({ match: { url }}) => (
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
                            render={({ match: { url }}) => (
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
