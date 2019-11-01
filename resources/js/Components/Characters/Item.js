import { faSunrise, faSunset, faThList, faTrashAlt } from "@fortawesome/pro-duotone-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import PropTypes from "prop-types";
import React, { PureComponent } from "react";
import { Link } from "react-router-dom";
import { renderActionList } from "../../helpers";
import { character } from "../../vendor/data";
import DpsParsesList from "../DpsParses/List";

class Item extends PureComponent {
    renderDpsParses = character => {
        const pendingDpsParsesRendered = character.dps_parses_pending.length
            ? [
                  <article className="col-lg-24 mt-5" key="pending-parses">
                      <h3>Latest 10 DPS Parses Pending Inspection</h3>
                      <DpsParsesList character={character} dpsParses={character.dps_parses_pending.slice(0, 10)} />
                  </article>,
              ]
            : [];
        const processedDpsParsesRendered = character.dps_parses_processed.length
            ? [
                  <article className="col-lg-24 mt-5 mb-5" key="processed-parses">
                      <h3>Latest 10 DPS Parses Approved</h3>
                      <DpsParsesList character={character} dpsParses={character.dps_parses_processed.slice(0, 10)} />
                  </article>,
              ]
            : [];

        return [...pendingDpsParsesRendered, ...processedDpsParsesRendered];
    };

    render = () => {
        const { character, deleteHandler, rerankHandler } = this.props;
        const actionList = {
            return: (
                <Link to={"/users/" + character.owner.id} title="Back to User">
                    <FontAwesomeIcon icon={faThList} />
                </Link>
            ),
            promote:
                typeof rerankHandler === "function" && character["role"].indexOf("DD") === -1 ? (
                    <a href="#" onClick={rerankHandler} data-id={character.id} data-action="promote" title="Promote Character">
                        <FontAwesomeIcon icon={faSunrise} />
                    </a>
                ) : null,
            demote:
                typeof rerankHandler === "function" && character["role"].indexOf("DD") === -1 ? (
                    <a href="#" onClick={rerankHandler} data-id={character.id} data-action="demote" title="Demote Character">
                        <FontAwesomeIcon icon={faSunset} />
                    </a>
                ) : null,
            delete:
                typeof deleteHandler === "function" && character.approved_for_tier === 0 ? (
                    <Link to="#" onClick={deleteHandler} data-id={character.id} title="Delete Character">
                        <FontAwesomeIcon icon={faTrashAlt} />
                    </Link>
                ) : null,
        };

        const characterContent = character.content
            .map(content => ({ id: content.id, name: content.name.concat(" ", content.version || "") }))
            .reduce((acc, curr) => [acc, " ", <li key={curr.id}>{curr.name}</li>], "");
        const characterSets = character.sets
            .map(set => (
                <a key={set["id"]} href={"https://eso-sets.com/set/" + set["slug"]} className="badge badge-dark" target="_blank">
                    {set["name"]}
                </a>
            ))
            .reduce((acc, curr) => [acc, " ", <li key={curr.key}>{curr}</li>], "");
        const characterSkills = character.skills
            .map(skill => (
                <a key={skill["id"]} href={"https://eso-skillbook.com/skill/" + skill["slug"]} className="badge badge-dark" target="_blank">
                    {skill["name"]}
                </a>
            ))
            .reduce((acc, curr) => [acc, " ", <li key={curr.key}>{curr}</li>], "");

        return [
            <section className="col-md-24 p-0 mb-4 d-flex flex-wrap" key="character">
                <h2 className="form-title col-md-24">{character.name}</h2>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <dl className="col-lg-8">
                    <dt>Ingame ID</dt>
                    <dd>{"@" + character.owner.name}</dd>

                    <dt>Class</dt>
                    <dd>{character.class}</dd>

                    <dt>Role</dt>
                    <dd>{character.role}</dd>

                    <dt>Content Clearance</dt>
                    <dd>{character.approved_for_tier ? 'Tier-' + character.approved_for_tier : 'None'}</dd>
                </dl>
                <article className="col-lg-6">
                    <h3>Content Cleared</h3>
                    {characterContent.length ? <ul>{characterContent}</ul> : "None"}
                </article>
                <article className="col-lg-5">
                    <h3>Sets Acquired</h3>
                    {characterSets.length ? <ul>{characterSets}</ul> : "None"}
                </article>
                <article className="col-lg-5">
                    <h3>Skills Leveled</h3>
                    {characterSkills.length ? <ul>{characterSkills}</ul> : "None"}
                </article>
                {this.renderDpsParses(character)}
            </section>,
        ];
    };
}

Item.propTypes = {
    character,
    deleteHandler: PropTypes.func, // based on existense of this param, we render Delete button
    rerankHandler: PropTypes.func, // based on existense of this param, we render Rerank buttons
};

export default Item;
