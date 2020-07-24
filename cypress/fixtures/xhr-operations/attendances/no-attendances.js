export const noAttendances = cy => {
    cy.fixture('xhr-data/attendances/no-attendances.json').as('noAttendances');
    cy.route('GET', '/api/attendances/6', '@noAttendances');
    cy.route('GET', '/api/attendances/347', '@noAttendances');
};
