import React from 'react';

export const authorizeAdmin = ({ me, groups }) => {
    if (!me || !groups || !me.linkedAccountsParsed.ips) {
        return false;
    }

    const myGroup = Object.entries(groups).find(group => me.linkedAccountsParsed.ips.remote_primary_group === group[1]['ipsGroupId']);

    return !(!myGroup || !myGroup[1]['isAdmin']);
};

export const authorizeUser = ({ me, groups }, withAdditionalPrechecks = false) => {
    if (!me || !groups || !me.linkedAccountsParsed.discord) {
        return false;
    }

    if (withAdditionalPrechecks && (!me.name || !me.name.length)) {
        return false;
    }

    const discordGroups = me.linkedAccountsParsed.discord.remote_secondary_groups;
    if (discordGroups && discordGroups.length) {
        const listOfUserGroups = discordGroups.find(discordRole => {
            const matchingGroup = Object.entries(groups).find(group => discordRole === group[1]['discordRole']);

            return matchingGroup === undefined ? false : matchingGroup['1']['isMember'];
        });

        return listOfUserGroups !== undefined;
    }

    return false;
};

export const renderActionList = actionList => {
    const actionListRendered = [];
    for (const [actionType, link] of Object.entries(actionList)) {
        if (link) {
            actionListRendered.push(<li key={actionType}>{link}</li>);
        }
    }

    return actionListRendered;
};

export const filter = function (event, typeUpdating) {
    const temp = Object.assign({}, this.state.filters);
    for (const [type, value] of Object.entries(temp)) {
        if (type === typeUpdating) {
            temp[type] = !value;
            event.currentTarget.classList.toggle('inactive');
        } else {
            temp[type] = value;
        }
    }
    this.setState({
        filters: temp,
    });
};
