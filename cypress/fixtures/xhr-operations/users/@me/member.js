export const stubMemberWithNoName = cy => {
    cy.fixture('xhr-data/users/member-with-no-name.json').as('member');
    cy.route('GET', '/api/users/@me', '@member');
};

export const stubUpdateName = cy => {
    cy.fixture('xhr-data/users/member.json').as('member');
    cy.route({
        method: 'POST',
        url: '/api/users/@me',
        response: '@member'
    }).as('updateMePostRequest');
};

export const stubMember = cy => {
    cy.fixture('xhr-data/users/member.json').as('member');
    cy.route('GET', '/api/users/@me', '@member');
};
