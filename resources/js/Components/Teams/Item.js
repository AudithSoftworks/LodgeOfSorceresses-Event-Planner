import { faChevronCircleLeft, faSunrise, faSunset, faTrashAlt } from "@fortawesome/pro-light-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import PropTypes from "prop-types";
import React, { PureComponent } from "react";
import { Link } from "react-router-dom";
import { renderActionList } from "../../helpers";
import { team } from "../../vendor/data";

class Item extends PureComponent {
    renderMembers = team => {
        return team.members.length
            ? []
            : [];
    };

    render = () => {
        const { team, deleteHandler, changeTierHandler } = this.props;
        const actionList = {
            return: (
                <Link to={"/teams"} title="Back to Teams">
                    <FontAwesomeIcon icon={faChevronCircleLeft} />
                </Link>
            ),
            tierIncrease:
                typeof changeTierHandler === "function" && team.tier < 4 ? (
                    <a href="#" onClick={changeTierHandler} data-id={team.id} data-action="increase-tier" title="Increase Tier">
                        <FontAwesomeIcon icon={faSunrise} />
                    </a>
                ) : null,
            tierDecrease:
                typeof changeTierHandler === "function" && team.tier > 1 ? (
                    <a href="#" onClick={changeTierHandler} data-id={team.id} data-action="decrease-tier" title="Decrease Tier">
                        <FontAwesomeIcon icon={faSunset} />
                    </a>
                ) : null,
            delete:
                typeof deleteHandler === "function" ? (
                    <Link to="#" onClick={deleteHandler} data-id={team.id} title="Delete Team">
                        <FontAwesomeIcon icon={faTrashAlt} />
                    </Link>
                ) : null,
        };

        return [
            <section className="col-md-24 p-0 mb-4 d-flex flex-wrap" key="character">
                <h2 className="form-title col-md-24">{team.name + ' Team Profile'}</h2>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <dl className="col-lg-8">
                    <dt>Tier</dt>
                    <dd>{team.tier}</dd>

                    <dt>Leader</dt>
                    <dd>{'@' + team.led_by.name}</dd>

                    <dt># of Members</dt>
                    <dd>{team.members.length}</dd>
                </dl>
                <article className="col-lg-16">
                    <h3>Member List</h3>
                    {team.members.length ? <ul>Coming Soon!</ul> : "None"}
                </article>
                {this.renderMembers(team)}
            </section>
        ];
    };
}

Item.propTypes = {
    team,
    deleteHandler: PropTypes.func, // based on existense of this param, we render Delete button
    changeTierHandler: PropTypes.func, // based on existense of this param, we render ChangeTier buttons
};

export default Item;
