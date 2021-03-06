import React, { Fragment, PureComponent, Suspense } from 'react';
import { connect } from 'react-redux';
import { Route, Switch } from 'react-router-dom';
import NoMatch from '../../Controllers/NoMatch';
import { authorizeUser } from '../../helpers';
import { user } from '../../vendor/data';
import Loading from '../Loading';

const Init = React.lazy(() => import(/* webpackPreload: true, webpackChunkName: "controllers-init" */ '../../Controllers/Init'));
const Onboarding = React.lazy(() => import(/* webpackPrefetch: 50, webpackChunkName: "controllers-onboarding" */ '../../Controllers/Onboarding'));
const Home = React.lazy(() => import(/* webpackPrefetch: 50, webpackChunkName: "controllers-home" */ '../../Controllers/Home'));
const Dashboard = React.lazy(() => import(/* webpackPrefetch: 50, webpackChunkName: "controllers-dashboard" */ '../../Controllers/Auth/Home'));
const Users = React.lazy(() => import(/* webpackPrefetch: 40, webpackChunkName: "controllers-users" */ '../../Controllers/Users'));
const Characters = React.lazy(() => import(/* webpackPrefetch: true, webpackChunkName: "controllers-characters" */ '../../Controllers/Characters'));
const MyCharacters = React.lazy(() => import(/* webpackPrefetch: 40, webpackChunkName: "controllers-my-characters" */ '../../Controllers/Auth/Characters'));
const CharacterForm = React.lazy(() => import(/* webpackPrefetch: true, webpackChunkName: "controllers-character_form" */ '../../Controllers/Forms/CharacterForm'));
const DpsParses = React.lazy(() => import(/* webpackPrefetch: true, webpackChunkName: "controllers-dps-parses" */ '../../Controllers/DpsParses'));
const DpsParseForm = React.lazy(() => import(/* webpackPrefetch: true, webpackChunkName: "controllers-dps_parse_form" */ '../../Controllers/Forms/DpsParseForm'));
// const Events = React.lazy(() => import(/* webpackPrefetch: 40, webpackChunkName: "controllers-events" */ "../../Controllers/Events"));
// const EventForm = React.lazy(() => import(/* webpackPrefetch: true, webpackChunkName: "controllers-event_form" */ "../../Controllers/Forms/EventForm"));
const Teams = React.lazy(() => import(/* webpackPrefetch: 40, webpackChunkName: "controllers-teams" */ '../../Controllers/Teams'));
const TeamForm = React.lazy(() => import(/* webpackPrefetch: true, webpackChunkName: "controllers-team_form" */ '../../Controllers/Forms/TeamForm'));
const TeamMembershipTerms = React.lazy(() => import(/* webpackPrefetch: true, webpackChunkName: "controllers-team_membership_terms" */ '../../Controllers/Forms/TeamMembershipTerms'));
const AdminHome = React.lazy(() => import(/* webpackPrefetch: 40, webpackChunkName: "controllers-admin-home" */ '../../Controllers/Admin/Home'));
const AdminDpsParses = React.lazy(() => import(/* webpackPrefetch: true, webpackChunkName: "controllers-admin-dps-parses" */ '../../Controllers/Admin/DpsParses'));

class Main extends PureComponent {
    constructor(props) {
        super(props);
        this.authorizeUser = authorizeUser.bind(this);
    }

    fetchUserRoutes = () => {
        return this.authorizeUser(true)
            ? [
                <Route exact path="/users" component={props => <Users {...props} />} key="/users" />,
                <Route exact path="/users/:id(\d+)" component={props => <Users {...props} />} key="/users/:id" />,
                <Route exact path="/characters/:id(\d+)" component={props => <Characters {...props} />} key="/characters/:id" />,
                // <Route exact path="/events" component={props => <Events {...props} />} key="/events" />,
                // <Route exact path="/events/create" component={props => <EventForm {...props} />} key="/events/create" />,
                <Route
                    key="/teams"
                    path="/teams"
                    render={({ match: { url } }) => (
                        <Fragment>
                            <Route exact path={url} component={props => <Teams {...props} />} />
                            <Route path={url + '/create'} component={props => <TeamForm {...props} />} />
                            <Route path={url + '/:id(\\d+)/characters/:cId(\\d+)'} component={props => <TeamMembershipTerms {...props} />} />
                            <Route path={url + '/:id(\\d+)/edit'} component={props => <TeamForm {...props} />} />
                            <Route exact path={url + '/:id(\\d+)'} component={props => <Teams {...props} />} />
                        </Fragment>
                    )}
                />,
                <Route
                    key="/@me"
                    path="/@me"
                    render={({ match: { url } }) => (
                        <Fragment>
                            <Route exact path={url} component={props => <Dashboard {...props} />} />
                            <Route exact path={url + '/characters'} component={props => <MyCharacters {...props} />} />
                            <Route path={url + '/characters/create'} component={props => <CharacterForm {...props} />} />
                            <Route path={url + '/characters/:id(\\d+)/edit'} component={props => <CharacterForm {...props} />} />
                            <Route path={url + '/characters/:id(\\d+)/parses/create'} component={props => <DpsParseForm {...props} />} />
                            <Route exact path={url + '/characters/:id(\\d+)/parses'} component={props => <DpsParses {...props} />} />
                        </Fragment>
                    )}
                />,
            ]
            : [];
    };

    fetchAdminRoutes = me => {
        return me && me.isAdmin
            ? [
                <Route
                    key="/admin"
                    path="/admin"
                    render={({ match: { url } }) => (
                        <Fragment>
                            <Route exact path={url} component={props => <AdminHome {...props} />} />
                            <Route path={url + '/parses'} component={props => <AdminDpsParses {...props} />} />
                        </Fragment>
                    )}
                />,
            ]
            : [];
    };

    render = () => {
        const { me } = this.props;

        return [
            <main key="main" className="container">
                <Suspense fallback={<Loading />}>
                    <Switch>
                        <Route exact path="/" component={props => <Init {...props} />} />
                        <Route exact path="/onboarding/:mode(members|soulshriven)" component={props => <Onboarding {...props} />} />
                        <Route exact path="/home" component={props => <Home {...props} />} />
                        {[...this.fetchUserRoutes(me)]}
                        {[...this.fetchAdminRoutes(me)]}
                        <Route component={NoMatch} />
                    </Switch>
                </Suspense>
            </main>,
        ];
    };
}

Main.propTypes = {
    me: user,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
});

export default connect(mapStateToProps, mapDispatchToProps)(Main);
