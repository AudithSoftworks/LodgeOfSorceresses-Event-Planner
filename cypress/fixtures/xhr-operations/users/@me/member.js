export const stubMemberWithNoName = cy => {
    cy.fixture('xhr-data/users/member-with-no-name.json').as('member');
    cy.route({
        method: 'GET',
        url: '/api/users/@me',
        delay: 1000,
        response: '@member'
    }).as('loadMemberWithNoName');
};

export const stubMember = cy => {
    cy.fixture('xhr-data/users/member.json').as('member');
    cy.route({
        method: 'GET',
        url: '/api/users/@me',
        delay: 1000,
        response: '@member'
    }).as('loadMember');
};

export const stubUpdateName = cy => {
    cy.fixture('xhr-data/users/member.json').as('member');
    cy.route({
        method: 'POST',
        url: '/api/users/@me',
        response: '@member'
    }).as('updateMePostRequest');
};
