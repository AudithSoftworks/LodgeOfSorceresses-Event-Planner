// noinspection ES6CheckImport
import { ConnectedRouter } from 'connected-react-router/immutable';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from "react-redux";
import getGroupsAction from "../actions/get-groups";
import getUserAction from "../actions/get-user";
import Footer from '../Components/Layout/Footer';
import Header from '../Components/Layout/Header';
import Main from '../Components/Layout/Main';
import Loading from "../Components/Loading";
import { user } from "../vendor/data";

class Application extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            precheckDone: false,
        };
    };

    componentDidMount = () => {
        const { me } = this.props;
        this.props.getGroupsAction()
            .then(() => {
                if (!me) {
                    this.props.getUserAction()
                        .then(() => {
                            this.setState({ precheckDone: true });
                        });
                }
            });
    };

    render = () => {
        const { precheckDone } = this.state;
        if (!precheckDone) {
            return <Loading message="Checking session..." />;
        }

        const { history } = this.props;
        document.querySelector('body').setAttribute('data-initialized', 'true');

        return (
            <ConnectedRouter history={history}>
                <Header />
                <Main />
                <Footer />
            </ConnectedRouter>
        );
    };
}

Application.propTypes = {
    history: PropTypes.object.isRequired,

    me: user,
    groups: PropTypes.object,
    getUserAction: PropTypes.func.isRequired,
    getGroupsAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    groups: state.getIn(['groups']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    getUserAction: () => dispatch(getUserAction()),
    getGroupsAction: () => dispatch(getGroupsAction()),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Application);
