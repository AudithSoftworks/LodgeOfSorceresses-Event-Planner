import PropTypes from "prop-types";
import React, { PureComponent } from "react";
import { connect } from "react-redux";
import { Link } from "react-router-dom";
import Notification from "../../Components/Notification";
import { characters } from "../../vendor/data";

class Home extends PureComponent {
    render = () => {
        return [
            <section className="col-md-24 p-0 mb-4" key="characterList">
                <h2 className="form-title col-md-24">Dashboard</h2>
                <article className="col-md-24">
                    <h3>Available actions</h3>
                    <ul>
                        <li>
                            <Link to="/admin/parses" title="Approve Parses">
                                DPS Parses pending Approval
                            </Link>
                        </li>
                    </ul>
                </article>
                <Notification key="notifications" />
            </section>,
        ];
    };
}

Home.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    notifications: PropTypes.array,
    allCharacters: characters,
};

const mapStateToProps = state => ({
    notifications: state.getIn(["notifications"]),
    allCharacters: state.getIn(["allCharacters"]),
});

export default connect(mapStateToProps)(Home);
